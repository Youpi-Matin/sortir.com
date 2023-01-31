<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Service\ParticipantUploadService;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ParticipantType;
use App\Form\ParticipantUploadType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    /**
     *
     * Edit Profil
     *
     */
    #[Route('/profil/edit/{id}', name: 'participant_edit', requirements: ['id' => '\d+'])]
    public function edit(Participant $participant, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher): Response
    {

        // Interdit l'acces si non authentifié
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($this->getUser() !== $participant) {
            throw $this->createAccessDeniedException('Impossible d\'acceder à cette page !');
        }

        $oldPassword = $this->getUser()->getPassword();

        $formulaireParticipant = $this->createForm(ParticipantType::class, $participant);

        $formulaireParticipant->handleRequest($request);

        if ($formulaireParticipant->isSubmitted() && $formulaireParticipant->isValid()) {
            if (empty($participant->getPassword())) {
                $participant->setPassword($oldPassword);
            } else {
                $participant->setPassword($hasher->hashPassword($participant, $participant->getPassword()));
            }

            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', "Profil modifié !");
        }

        return $this->render('participant/edit.html.twig', [
            'formulaireParticipant' => $formulaireParticipant->createView(),
            'participant' => $participant
        ]);
    }

    /**
     *
     * Show Profil
     *
     */
    #[Route('/profil/{id}', name: 'participant_view')]
    public function view(Participant $participant): Response
    {
        // Interdit l'acces si non authentifié
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('participant/view.html.twig', [
            'participant' => $participant
        ]);
    }

    /**
     *
     * Upload
     *
     */
    #[Route('/upload/', name: 'participant_upload')]
    public function upload(Request $request, ParticipantUploadService $participantUploadService): Response
    {
        // Interdit l'acces si non authentifié
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $formulaireUploadParticipants = $this->createForm(ParticipantUploadType::class);

        $formulaireUploadParticipants->handleRequest($request);

        if ($formulaireUploadParticipants->isSubmitted() && $formulaireUploadParticipants->isValid()) {
            // get file in filebag objects
            $file = $request->files->get('participant_upload')['participantListeFile']['file'];

            // Upload participant
            $participantUploadService->importParticipants($file);

            $this->addFlash('success', "Liste importée !");
        }

        return $this->render('participant/upload.html.twig', [
            'formulaireUploadParticipants' => $formulaireUploadParticipants->createView()
        ]);
    }
}
