<?php

namespace App\Controller;

use App\Entity\Participant;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Form\ProfilUtilisateurType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateurController extends AbstractController
{
    #[Route('/profil/{id}', name: 'utilisateur_edit_profil')]
    public function index(int $id): Response
    {
		$utilisateur = new Participant();
		$formulaireProfilUtilisateur = $this->createForm(ProfilUtilisateurType::class, $utilisateur);

        return $this->render('utilisateur/profil.html.twig', [
			'formulaireProfilUtilisateur' => $formulaireProfilUtilisateur->createView(),
            'controller_name' => 'UtilisateurController',
        ]);
    }
}
