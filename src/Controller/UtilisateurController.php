<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateurController extends AbstractController
{
    #[Route('/utilisateur-profil', name: 'utilisateur_profil')]
    public function index(): Response
    {
        return $this->render('utilisateur/profil.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }
}
