<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\SortieFiltre;
use App\Form\SortieFiltreType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/', name: 'sortie_index')]
    public function index(
        Request $request,
        SortieRepository $sortieRepository
    ): Response {
        /** @var Participant */
        $user = $this->getUser();

        $filtre = (new SortieFiltre())
            ->setCampus($user->getCampus())
            ->setOrganisateurice(false)
            ->setInscrite(false)
            ->setNoninscrite(false);

        $formFiltre = $this->createForm(SortieFiltreType::class, $filtre);
        $formFiltre->handleRequest($request);

        if ($formFiltre->isSubmitted() && !$formFiltre->isValid()) {
            // En cas d'erreurs, on renvoie toujours des résultats ?
            // On récupère les champs problématiques et on les valorise à null
            // sauf le campus que l'on revalorise avec celui de l'utilisateur
            $errors = $formFiltre->getErrors(true);
            foreach ($errors as $error) {
                $param = $error->getOrigin()->getPropertyPath()->getElement(0);
                if ($param !== 'campus') {
                    $methode = 'set' . ucfirst($param);
                    $filtre->$methode(null);
                } else {
                    $filtre->setCampus($user->getCampus());
                }
            }
        }

        $sorties = $sortieRepository->findByFiltre($filtre, $user);

        return $this->render('sortie/index.html.twig', [
            'form' => $formFiltre,
            'sorties' => $sorties,
        ]);
    }
}
