<?php

namespace App\Service;

use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use League\Csv\Reader;

class ParticipantUploadService
{
    protected $entityManager;
    protected $participantRepository;
    protected $campusRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParticipantRepository $participantRepository,
        CampusRepository $campusRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->participantRepository = $participantRepository;
        $this->campusRepository = $campusRepository;
    }

    public function importParticipants(UploadedFile $file)
    {
        //CSV Reader
        $csvReader = Reader::createFromPath($file->getPathName(), 'r');
        $csvReader->setHeaderOffset(0);

        foreach ($csvReader as $participantData) {
            $participant = $this->createParticipant($participantData);
            $this->entityManager->persist($participant);
        }

        $this->entityManager->flush();
    }

    private function createParticipant(array $participantData): Participant
    {
        $participant = $this->participantRepository->findOneBy(['mail' => $participantData['mail']]);
        if (!$participant) {
            $participant = new Participant();
        }

        // get campus
        $campus = $this->campusRepository->findOneBy(['id' => $participantData['campus_id']]);

        $participant->setCampus($campus)
            ->setMail($participantData['mail'])
            ->setPassword($participantData['mot_passe'])
            ->setNom($participantData['nom'])
            ->setPrenom($participantData['prenom'])
            ->setPseudo($participantData['pseudo'])
            ->setTelephone($participantData['telephone'])
        ;

        return $participant;
    }
}
