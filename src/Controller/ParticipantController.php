<?php

namespace App\Controller;

use App\Entity\Participant;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Form\ParticipantType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    #[Route('/profil/edit/{id}', name: 'participant_edit')]
    public function edit(Participant $participant): Response
    {
		$formulaireParticipant = $this->createForm(ParticipantType::class, $participant);

        return $this->render('participant/edit.html.twig', [
			'formulaireParticipant' => $formulaireParticipant->createView()
        ]);
    }

	#[Route('/profil/{id}', name: 'participant')]
    public function profil(Participant $participant): Response
    {
		return $this->render('participant/profil.html.twig', [
			'participant' => $participant
        ]);
    }
}
