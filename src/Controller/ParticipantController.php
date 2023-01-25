<?php

namespace App\Controller;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ParticipantType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    #[Route('/profil/edit/{id}', name: 'participant_edit', requirements: ['id' => '\d+'])]
    public function edit(Participant $participant, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher): Response
    {
        if ($this->getUser()->getId() != $participant->getId()) {
            return $this->redirectToRoute('sortie_index');
        }

        $currentPassword = $this->getUser()->getPassword();

        $formulaireParticipant = $this->createForm(ParticipantType::class, $participant);

        $formulaireParticipant->handleRequest($request);

        if ($formulaireParticipant->isSubmitted() && $formulaireParticipant->isValid()) {
            $participant->setPassword($participant->getPassword());
            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', "Profil modifié !");
        }

        return $this->render('participant/edit.html.twig', [
            'formulaireParticipant' => $formulaireParticipant->createView()
        ]);
    }

    #[Route('/profil/{id}', name: 'participant_view')]
    public function view(Participant $participant): Response
    {
        return $this->render('participant/view.html.twig', [
        'participant' => $participant
        ]);
    }
}
