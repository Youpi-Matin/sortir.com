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
    public function edit(
        Participant $participant,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher
    ): Response {
        // Interdit l'acces si non authentifié
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var Participant $user */
        $user = $this->getUser();

        if ($user !== $participant and !$user->isAdministrateur()) {
            throw $this->createAccessDeniedException('Impossible d\'acceder à cette page !');
        }

        $oldPassword = $user->getPassword();

        /**
         * SI l'utilisateur connecté est admlinistrateur, alors on affiche le choix du campus dans le formulaire
         */
        if ($user->isAdministrateur()) {
            $formOptions = array(
                'canEditCampus' => true,
                'canEditPassword' => false,
            );
        } else {
            $formOptions = array(
                'canEditCampus' => false,
                'canEditPassword' => true,
            );
        }

        $formulaireParticipant = $this->createForm(ParticipantType::class, $participant, $formOptions);

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

    #[Route('/profil/{id}', name: 'participant_view')]
    public function view(Participant $participant): Response
    {
        // Interdit l'acces si non authentifié
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('participant/view.html.twig', [
            'participant' => $participant
        ]);
    }
}
