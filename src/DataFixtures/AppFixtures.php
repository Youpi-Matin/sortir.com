<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $slugger = new AsciiSlugger();

        $etats = [];
        $villes = [];
        $campus = [];
        $lieux = [];
        $participants = [];

        $states = ['Créée', 'Ouverte', 'Clôturée', 'Activité en cours', 'Passée', 'Annulée'];

        foreach ($states as $state) {
            $etat = new Etat();
            $etat->setLibelle($state);

            $etats[] = $etat;

            $manager->persist($etat);
        }

        for ($i = 0; $i < 5; $i++) {
            $ville = new Ville();
            $ville->setCodePostal(rand(01001, 99999));
            $ville->setNom($faker->city());

            $villes[] = $ville;

            $manager->persist($ville);
        }

        for ($i = 0; $i < 3; $i++) {
            $city = new Campus();
            $city->setNom($faker->city());

            $campus[] = $city;

            $manager->persist($city);
        }

        for ($i = 0; $i < 50; $i++) {
            $participant = new Participant();
            $participant->setActif(true);

            $i === 1 ? $participant->setAdministrateur(true) : $participant->setAdministrateur(false);

            $participant->setNom($faker->lastName());
            $prenom = $faker->firstName();
            $participant->setPrenom($prenom);
            $participant->setPseudo($slugger->slug($prenom)->lower() . uniqid());
            $participant->setPassword($this->hasher->hashPassword($participant, 'dev'));
            $participant->setMail($faker->email());
            $participant->setTelephone($faker->phoneNumber());
            $participant->setCampus($campus[array_rand($campus)]);

            $participants[] = $participant;

            $manager->persist($participant);
        }

        for ($i = 0; $i < 50; $i++) {
            $lieu = new Lieu();
            $lieu->setNom($faker->sentence(3));
            $lieu->setRue($faker->streetAddress());
            $lieu->setVille($villes[array_rand($villes)]);
            $coordinates = $faker->localCoordinates();
            $lieu->setLatitude($coordinates['latitude']);
            $lieu->setLongitude($coordinates['longitude']);

            $lieux[] = $lieu;

            $manager->persist($lieu);
        }


        for ($i = 0; $i < 25; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($faker->sentence(3));
            $futureDate = $faker->dateTimeBetween('-3 months', '+3 months');
            $sortie->setDateHeureDebut($futureDate);
            $sortie->setDateLimiteInscription($futureDate->modify('-10 days'));
            $sortie->setDuree(rand(20, 240));

            $places = rand(5, 40);

            $sortie->setNbInscriptionsMax($places);
            $sortie->setInfosSortie($faker->paragraphs(3, true));
            $sortie->setEtat($etats[array_rand($etats)]);
            $sortie->setLieu($lieux[array_rand($lieux)]);

            /** @var Participant */
            $organisateur = $participants[array_rand($participants)];
            $sortie->setOrganisateur($organisateur);
            $sortie->setCampus($organisateur->getCampus());

            for ($j = 0; $j < $places; $j++) {
                $sortie->addParticipant($participants[array_rand($participants)]);
            }

            $manager->persist($sortie);
        }

        $manager->flush();
    }
}
