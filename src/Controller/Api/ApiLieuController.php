<?php

namespace App\Controller\Api;

use App\Entity\Ville;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Annotation\Groups;

class ApiLieuController extends AbstractController
{
    #[Route('/api/lieu/ville/{id}', name: 'api_lieux_ville')]
    public function api_lieux_ville($id, EntityManagerInterface $manager): JsonResponse
    {
        $result = $manager->getRepository(Ville::class)->createQueryBuilder('v')
            ->join('v.lieux', 'l')
            ->addSelect('l')
            ->andWhere('v.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getSingleResult();

        return $this->json($result, Response::HTTP_OK, [], ['groups' => 'liste_lieux']);
    }
}
