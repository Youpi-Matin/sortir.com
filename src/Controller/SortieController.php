<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Model\SortieFiltre;
use App\Form\SortieCreationType;
use App\Form\SortieFiltreType;
use App\Repository\SortieRepository;
use App\Service\SortieAvantInscription;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/', name: 'sortie_index')]
    public function index(
        Request $request,
        SortieRepository $sortieRepository
    ): Response {
        /** @var Participant */
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

        if (SortieAvantInscription::dansLesTemps($sortie) && SortieAvantInscription::placesDisponibles($sortie)) {
            $sortie->addParticipant($this->getUser());
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
                    'message' => 'Il n\'est plus possible de s\'inscrire pour cette sortie, soit parce que le nombre maximum de place est atteint, soit parce que la date limite d\'inscription est passée.',
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
        $sortie->removeParticipant($this->getUser());
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

    #[Route('/sortie/create', name: 'sortie_create', methods: ['GET', 'POST'])]
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        // Interdit l'acces si non authentifié
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // Récupère l'organisteur
        /** @var \App\Entity\Participant $user */
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
            $lieu = $sortie->getLieu();
            $ville = $lieu->getVille();
            $manager = $doctrine->getManager();
            $manager->persist($ville);
            $manager->persist($lieu);
            $manager->persist($sortie);
            $manager->flush();

            return $this->redirectToRoute('sortie_index');
        }

        return $this->render('sortie/create.html.twig', [
            'form' => $form,
            'etat' => $sortie->getEtat()->getLibelle(),
            'sortie_id' => $sortie->getId(),
        ]);
    }

    #[Route('/sortie/manage/{id}', name: 'sortie_manage', methods: ['GET', 'POST'])]
    public function manage(int $id, Request $request, ManagerRegistry $doctrine): Response
    {
        // Interdit l'acces si non authentifié
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Récupère l'orginisateur de la sortie
        $sortie = $doctrine->getRepository(Sortie::class)->findOneBy(['id' => $id]);

        // Si l'utilisateur n'est pas l'organisateur -> Acccess Denied
        if ($sortie->getOrganisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Impossible d\'acceder à cette page !');
        }

        $form = $this->createForm(SortieCreationType::class, $sortie);

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

        return $this->render('sortie/create.html.twig', [
            'form' => $form,
            'etat' => $sortie->getEtat()->getLibelle(),
            'sortie_id' => $sortie->getId(),
        ]);
    }

    #[Route('sortie/cancel/{id}', name: 'sortie_cancel')]
    public function cancel(int $id, ManagerRegistry $doctrine): Response
    {
        // Interdit l'acces si non authentifié
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Récupère l'orginisateur de la sortie
        $sortie = $doctrine->getRepository(Sortie::class)->findOneBy(['id' => $id]);

        // Si l'utilisateur n'est pas l'organisateur -> Acccess Denied
        if ($sortie->getOrganisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Impossible d\'acceder à cette page !');
        }

        $sortie = $doctrine->getRepository(Sortie::class)->find($id);
        $sortie->setEtat($doctrine->getRepository(Etat::class)->findOneBy(['libelle' => 'Annulée']));
        $manager = $doctrine->getManager();
        $manager->persist($sortie);
        $manager->flush();

        $this->addFlash('success', 'Sortie Annulée avec succès');

        return $this->redirectToRoute('sortie_index');
    }

    #[Route('sortie/publish/{id}', name: 'sortie_publish')]
    public function publish(int $id, ManagerRegistry $doctrine): Response
    {
        // Interdit l'acces si non authentifié
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Récupère l'orginisateur de la sortie
        $sortie = $doctrine->getRepository(Sortie::class)->findOneBy(['id' => $id]);

        // Si l'utilisateur n'est pas l'organisateur -> Acccess Denied
        if ($sortie->getOrganisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Impossible d\'acceder à cette page !');
        }

        $sortie = $doctrine->getRepository(Sortie::class)->find($id);
        $sortie->setEtat($doctrine->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']));
        $manager = $doctrine->getManager();
        $manager->persist($sortie);
        $manager->flush();

        $this->addFlash('success', 'Sortie Ouverte aux inscriptions avec succès');

        return $this->redirectToRoute('sortie_index');
    }
}
