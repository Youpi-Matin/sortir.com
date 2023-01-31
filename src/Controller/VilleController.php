<?php

namespace App\Controller;

use App\Entity\Ville;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VilleController extends AbstractController
{
    #[Route('/ville/index', name: 'ville_index')]
    public function index(): Response
    {
        return $this->render('ville/index.html.twig', [
            'controller_name' => 'VilleController',
        ]);
    }

    #[Route('/ville/create', name: 'ville_create')]
    public function create(): Response
    {
        return $this->render('ville/index.html.twig', [
            'controller_name' => 'VilleController',
        ]);
    }

    #[Route('/ville/delete/{id}', name: 'ville_delete')]
    public function delete(Ville $ville, EntityManagerInterface $manager): Response
    {
        return $this->render('ville/index.html.twig', [
            'controller_name' => 'VilleController',
        ]);
    }
}
