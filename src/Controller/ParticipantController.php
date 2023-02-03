<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Service\ParticipantUploadService;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ParticipantType;
use App\Form\ParticipantUploadType;
use App\Form\ParticipantUpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    #[Route('/profil/add/', name: 'participant_add')]
    public function add(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher
    ): Response 
    {
        // Interdit l'acces si non authentifié
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $participant = new Participant();

        $formulaireParticipant = $this->createForm(ParticipantType::class, $participant);

        $formulaireParticipant->handleRequest($request);

        if ($formulaireParticipant->isSubmitted() && $formulaireParticipant->isValid()) {
            $participant->setPassword($hasher->hashPassword($participant, $participant->getPassword()));

            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', "Profil ajouté !");

            return $this->redirectToRoute('participant_edit', ['id'=>$participant->getId()]);
        } 

        return $this->render('participant/create.html.twig', [
            'formulaireParticipant' => $formulaireParticipant->createView(),
            'participant' => $participant
        ]);
    }

    /**
     *
     * Edit Profil
     *
     */
    #[Route('/profil/edit/{id}', name: 'participant_edit', requirements: ['id' => '\d+'])]
    public function edit(
        Participant $participant,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher
    ): Response 
    {
        // Interdit l'acces si non authentifié
        $this->denyAccessUnlessGranted('edit', $participant);

        /** @var Participant $user */
        $user = $this->getUser();

        $formulaireParticipant = $this->createForm(ParticipantUpdateType::class, $participant);

        $formulaireParticipant->handleRequest($request);

        if ($formulaireParticipant->isSubmitted() && $formulaireParticipant->isValid()) {
            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', "Profil modifié !");
        }

        return $this->render('participant/update.html.twig', [
            'formulaireParticipant' => $formulaireParticipant->createView(),
            'participant' => $participant
        ]);
    }

    /**
     *
     * Show Profil
     *
     */
    #[Route('/profil/{id<\d+>}', name: 'participant_view')]
    public function view(Participant $participant): Response
    {
        // Interdit l'acces si non authentifié
        $this->denyAccessUnlessGranted('view', $participant);

        return $this->render('participant/view.html.twig', [
            'participant' => $participant
        ]);
    }

    /**
     *
     * Upload
     *
     */
    #[Route('/profil/upload/', name: 'participant_upload')]
    public function upload(Request $request, ParticipantUploadService $participantUploadService): Response
    {
        // Interdit l'acces si non authentifié
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $formulaireUploadParticipants = $this->createForm(ParticipantUploadType::class);

        $formulaireUploadParticipants->handleRequest($request);

        if ($formulaireUploadParticipants->isSubmitted() && $formulaireUploadParticipants->isValid()) {
            // get file in filebag objects
            $file = $formulaireUploadParticipants->getData()['participantListeFile'];

            // Upload participant
            $participantUploadService->importParticipants($file);

            $this->addFlash('success', "Liste importée !");
        }

        return $this->render('participant/upload.html.twig', [
            'formulaireUploadParticipants' => $formulaireUploadParticipants->createView()
        ]);
    }
}
