<?php

namespace App\Controller\Api;

use App\Entity\Ville;
use App\Repository\LieuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiLieuController extends AbstractController
{
    #[Route('/api/lieu/ville/{id}', name: 'api_lieux_ville', methods: ['GET'])]
    public function apiLieuxVille(Ville $ville, LieuRepository $lieuRepository): Response
    {
        $lieux = $lieuRepository->findLieuxForOneVille($ville);

        return $this->json($lieux, Response::HTTP_OK);
    }
}
