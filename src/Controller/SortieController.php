<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieCancelType;
use App\Form\SortieUpdateType;
use App\Model\SortieFiltre;
use App\Form\SortieCreationType;
use App\Form\SortieFiltreType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Service\SortieAvantInscription;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/', name: 'sortie_index')]
    public function index(
        Request $request,
        SortieRepository $sortieRepository,
        ParticipantRepository $participantRepository,
    ): Response {
        // Récupération de la date du jour pour affichage page principale
        $today = new \DateTime('now');
        /** @var Participant $user */
        $user = $this->getUser();
        $filtre = (new SortieFiltre())
            ->setCampus($user->getCampus());

        $formFiltre = $this->createForm(SortieFiltreType::class, $filtre);
        $formFiltre->handleRequest($request);

        if ($formFiltre->isSubmitted() && !$formFiltre->isValid()) {
            // En cas d'erreurs, on renvoie toujours des résultats ?
            // On récupère les champs problématiques et on les valorise à null
            // sauf le campus que l'on revalorise avec celui de l'utilisateur
            $errors = $formFiltre->getErrors(true);
            foreach ($errors as $error) {
                $param = $error->getOrigin()->getPropertyPath()->getElement(0);
                if ($param !== 'campus') {
                    $methode = 'set' . ucfirst($param);
                    $filtre->$methode(null);
                } else {
                    $filtre->setCampus($user->getCampus());
                }
            }
        }

        $sorties = $sortieRepository->findByFiltre($filtre, $user);

        return $this->render('sortie/index.html.twig', [
            'form' => $formFiltre,
            'sorties' => $sorties,
            'today' => $today,
        ]);
    }

    #[Route(
        '/sortie/inscrire/{id<\d+>}',
        name: 'sortie_inscrire',
        methods: ['GET']
    )]
    public function inscrire(
        Sortie $sortie,
        SortieRepository $sortieRepository
    ): JsonResponse {
        // Interdit l'acces si non authentifié
        $this->denyAccessUnlessGranted('subscribe', $sortie);

        if (SortieAvantInscription::dansLesTemps($sortie) && SortieAvantInscription::placesDisponibles($sortie)) {
            /** @var Participant $user */
            $user = $this->getUser();
            $sortie->addParticipant($user);
            $sortieRepository->save($sortie, true);

            return new JsonResponse(
                [
                    'status' => 'ok',
                    'message' => 'Votre inscription a bien été prise en compte.',
                    'count' => count($sortie->getParticipants()),
                ],
                Response::HTTP_OK,
                [],
                false
            );
        } else {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Il n\'est plus possible de s\'inscrire pour cette sortie, soit parce que le nombre
                     maximum de place est atteint, soit parce que la date limite d\'inscription est passée.',
                ],
                Response::HTTP_OK,
                [],
                false
            );
        }
    }

    #[Route(
        '/sortie/desister/{id<\d+>}',
        name: 'sortie_desister',
        methods: ['GET']
    )]
    public function desister(
        Sortie $sortie,
        SortieRepository $sortieRepository
    ): JsonResponse {
        // Interdit l'acces si non authentifié
        $this->denyAccessUnlessGranted('unsubscribe', $sortie);

        /** @var Participant $user */
        $user = $this->getUser();
        $sortie->removeParticipant($user);
        $sortieRepository->save($sortie, true);

        return new JsonResponse(
            [
                'status' => 'ok',
                'message' => 'Votre désistement a bien été pris en compte.',
                'count' => count($sortie->getParticipants()),
            ],
            Response::HTTP_OK,
            [],
            false
        );
    }

    /** Creation d'un sortie
     * @param Request $request
     * @param ManagerRegistry $doctrine
     * @return Response
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    #[Route('/sortie/create', name: 'sortie_create', methods: ['GET', 'POST'])]
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        // Interdit l'acces si non authentifié
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // Récupère l'organisteur
        /** @var Participant $user */
        $user = $this->getUser();

        $sortie = new Sortie();
        $sortie->setOrganisateur($user);
        $sortie->setDuree(90);
        $sortie->setCampus($user->getCampus());
        $sortie->setDateHeureDebut(new \DateTime('tomorrow'));
        $sortie->setDateLimiteInscription(new \DateTime('now'));
        $sortie->setEtat($doctrine->getRepository(Etat::class)->findOneBy(['libelle' => 'Créée']));

        $form = $this->createForm(SortieCreationType::class, $sortie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->request->get('action_button') === 'publish') {
                $sortie->setEtat($doctrine->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']));
            }
            $manager = $doctrine->getManager();
            $manager->persist($sortie);
            $manager->flush();

            $this->addFlash('success', 'Sortie Crée avec succès');

            return $this->redirectToRoute('sortie_index');
        }

        return $this->render('sortie/create.html.twig', [
            'form' => $form,
            'etat' => $sortie->getEtat()->getLibelle(),
            'sortie_id' => $sortie->getId(),
        ]);
    }

    /** Publication d'une sortie
     * @param int $id
     * @param Request $request
     * @param ManagerRegistry $doctrine
     * @return Response
     */
    #[Route('sortie/publish/{id<\d+>}', name: 'sortie_publish')]
    public function publish(Sortie $sortie, ManagerRegistry $doctrine): Response
    {
        // Interdit l'acces si non authentifié
        $this->denyAccessUnlessGranted('edit', $sortie);

        // Si la sortie n'existe pas
        if (!$sortie) {
            throw $this->createNotFoundException('La sortie n\'existe pas');
        }

        $sortie->setEtat($doctrine->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']));
        $manager = $doctrine->getManager();
        $manager->persist($sortie);
        $manager->flush();

        $this->addFlash('success', 'Sortie Ouverte aux inscriptions avec succès');

        return $this->redirectToRoute('sortie_index');
    }

    /** Mise à jour d'une sortie
     * @param int $id
     * @param Request $request
     * @param ManagerRegistry $doctrine
     * @return Response
     */
    #[Route('/sortie/update/{id<\d+>}', name: 'sortie_update', methods: ['GET', 'POST'])]
    public function update(Sortie $sortie, Request $request, ManagerRegistry $doctrine): Response
    {
        // Interdit l'acces si pas l'organisateur ou pas admin
        $this->denyAccessUnlessGranted('edit', $sortie);

        // Si la sortie n'existe pas
        if (!$sortie) {
            throw $this->createNotFoundException('La sortie n\'existe pas');
        }

        $form = $this->createForm(SortieUpdateType::class, $sortie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lieu = $sortie->getLieu();
            $ville = $lieu->getVille();
            $manager = $doctrine->getManager();
            $manager->persist($ville);
            $manager->persist($lieu);
            $manager->persist($sortie);
            $manager->flush();

            return $this->redirectToRoute('sortie_index');
        }

        return $this->render('sortie/update.html.twig', [
            'form' => $form,
            'sortie_id' => $sortie->getId(),
        ]);
    }

    /** Annulation d'une sortie
     * Utilise un formaulaire contenant seulement le motif de l'annulation
     * Le motif remplacera les infos de la sortie.
     * @param int $id L'id de la sortie à annuler
     * @param Request $request
     * @param ManagerRegistry $doctrine
     * @return Response
     */
    #[Route('sortie/cancel/{id<\d+>}', name: 'sortie_cancel', methods: ['POST', 'GET'])]
    public function cancel(Sortie $sortie, Request $request, ManagerRegistry $doctrine): Response
    {
        // Interdit l'acces si pas l'organisateur ou pas admin ou sortie en cours
        $this->denyAccessUnlessGranted('cancel', $sortie);

        // Si la sortie n'existe pas
        if (!$sortie) {
            throw $this->createNotFoundException('La sortie n\'existe pas');
        }

        $form = $this->createForm(SortieCancelType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On remplace l'état de la sortie par 'Annulée'
            $sortie->setEtat($doctrine->getRepository(Etat::class)->findOneBy(['libelle' => 'Annulée']));
            // On remplace la description par le motif de l'annulation
            $sortie->setInfosSortie($form->get('motif')->getData());
            $manager = $doctrine->getManager();
            $manager->persist($sortie);
            $manager->flush();
            $this->addFlash('success', 'Sortie Annulée avec succès');
            return $this->redirectToRoute('sortie_index');
        }


        return $this->render('sortie/cancel.html.twig', [
            'form' => $form,
            'sortie' => $sortie,
        ]);
    }

    /** Suppression d'une sortie
     * @param int $id
     * @param ManagerRegistry $doctrine
     * @return Response
     */
    #[Route('sortie/delete/{id<\d+>}', name: 'sortie_delete')]
    public function delete(Sortie $sortie, ManagerRegistry $doctrine): Response
    {
        // Interdit l'acces si pas l'organisateur ou pas admin
        $this->denyAccessUnlessGranted('edit', $sortie);

        // Si la sortie n'existe pas
        if (!$sortie) {
            throw $this->createNotFoundException('La sortie n\'existe pas');
        }

        $manager = $doctrine->getManager();
        $manager->remove($sortie);
        $manager->flush();

        $this->addFlash('success', 'Sortie Supprimée avec succès');

        return $this->redirectToRoute('sortie_index');
    }

    /** Affichage d'une sortie
     * @param int $id
     * @param ManagerRegistry $doctrine
     * @return Response
     */
    #[Route('sortie/view/{id<\d+>}', name: 'sortie_view')]
    public function view(Sortie $sortie, ManagerRegistry $doctrine): Response
    {
        // Interdit l'acces si non authentifié
        $this->denyAccessUnlessGranted('view', $sortie);

        // Si la sortie n'existe pas
        if (!$sortie) {
            throw $this->createNotFoundException('La sortie n\'existe pas');
        }

        if ($sortie->getEtat()->getLibelle() === 'Archivée') {
            throw $this->createNotFoundException('La sortie n\'existe plus');
        }

        return $this->render('sortie/view.html.twig', [
            'sortie' => $sortie,
        ]);
    }
}
