<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\SortieFiltre;
use App\Form\SortieCreationType;
use App\Form\SortieFiltreType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/', name: 'sortie_index')]
    public function index(
        Request $request,
        ParticipantRepository $participantRepository,
        SortieRepository $sortieRepository
    ): Response {
        /** @var Participant */
        $user = $this->getUser();
        //$user = $participantRepository->findOneBy(['id' => 1]);

        $filtre = (new SortieFiltre())
            ->setCampus($user->getCampus())
            ->setOrganisateurice(false)
            ->setInscrite(false)
            ->setNoninscrite(false);

        $formFiltre = $this->createForm(SortieFiltreType::class, $filtre);
        $formFiltre->handleRequest($request);

        if ($formFiltre->isSubmitted() && $formFiltre->isValid()) {
        }

        $sorties = $sortieRepository->findByFiltre($filtre, $user);

        return $this->render('sortie/index.html.twig', [
            'form' => $formFiltre,
            'sorties' => $sorties,
        ]);
    }

    #[Route('/sortie/creation', name: 'sortie_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $manager, ParticipantRepository $participantRepository):Response
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

        $form = $this->createForm(SortieCreationType::class, $sortie);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($form);
            $manager->flush();
            return $this->redirectToRoute('sortie_index');
        }

        return $this->render('sortie/create.html.twig', [
            'form'=> $form,
        ]);
    }
}
