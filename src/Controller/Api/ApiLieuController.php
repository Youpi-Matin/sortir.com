<?php

namespace App\Controller\Api;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/api/lieu/ajout/', name: 'api_lieu_ajout', methods: ['GET', 'POST'])]
    public function apiLieuAjout(
        LieuRepository $lieuRepository,
        VilleRepository $villeRepository,
        Request $request
    ): JsonResponse {
        $lieu = new Lieu();

        if ($request->get('ville') && $request->get('ville') !== '') {
            $ville = $villeRepository->findOneBy([
                'id' => $request->get('ville')
            ]);
        } else {
            $ville = $villeRepository->findLast();
        }

        $lieu->setVille($ville);

        $form = $this->createForm(LieuType::class, $lieu);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lieu->setVille($ville);
            $lieuRepository->save($lieu, true);
            return $this->json([
                'id' => $lieu->getId(),
                'nom' => $lieu->getNom(),
            ]);
        }

        $content = $this->render('api/lieu/ajouter.html.twig', [
        'form' => $form,
        ]);

        return new JsonResponse($content->getContent());
    }
}
