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
use Faker\Core\DateTime;
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
        $etatSortie = [];

        $states = ['Créée', 'Ouverte', 'Clôturée', 'Activité en cours', 'Passée', 'Annulée', 'Archivée'];

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

        /*
         * Creation de l'utilisateur admin
         * login admin / Password dev
         */
        $participant = new Participant();
        $participant->setActif(true);
        $participant->setAdministrateur(true);
        $participant->setNom($faker->lastName());
        $prenom = $faker->firstName();
        $participant->setPrenom($prenom);
        $participant->setPseudo('admin');
        $participant->setPassword($this->hasher->hashPassword($participant, 'dev'));
        $participant->setMail($faker->email());
        $participant->setTelephone($faker->phoneNumber());
        $participant->setCampus($campus[array_rand($campus)]);
        $participants[] = $participant;
        $manager->persist($participant);

        /*
         * Creation de l'utilisateur user
         * login user / Password dev
         */
        $participant = new Participant();
        $participant->setActif(true);
        $participant->setAdministrateur(false);
        $participant->setNom($faker->lastName());
        $prenom = $faker->firstName();
        $participant->setPrenom($prenom);
        $participant->setPseudo('user');
        $participant->setPassword($this->hasher->hashPassword($participant, 'dev'));
        $participant->setMail($faker->email());
        $participant->setTelephone($faker->phoneNumber());
        $participant->setCampus($campus[array_rand($campus)]);
        $participants[] = $participant;
        $manager->persist($participant);

        /*
         * Generation de 48 utilisateurs aleatoires
         */
        for ($i = 0; $i < 48; $i++) {
            $participant = new Participant();
            $participant->setActif(true);
            $participant->setAdministrateur(false);
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


        for ($i = 0; $i < 50; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($faker->sentence(3));

            /*
             * Gestion des dates debut et datelimite inscription
             * La date de début est comprise en -30 et +30 jours par rapport à maintenant
             * La date limite est 7 jours avant la date de début
             */
            $now = new \DateTime();
            $dateDebut = $faker->dateTimeBetween('-60 days', '+90 days');
            $sortie->setDateHeureDebut($dateDebut);
            $dateLimiteInscriptionTimestamp = ($dateDebut->getTimestamp() - (7 * 24 * 3600)); // - 7 jours
            $dateLimiteInscription = (new \DateTime())->setTimestamp($dateLimiteInscriptionTimestamp);
            $sortie->setDateLimiteInscription($dateLimiteInscription);
            $sortie->setDuree(rand(20, 240));
            $places = rand(5, 40);
            $sortie->setNbInscriptionsMax($places);
            $sortie->setInfosSortie($faker->paragraphs(3, true));
            $sortie->setLieu($lieux[array_rand($lieux)]);

            /*
            * Gestion etat des sorties
            */
            // Si aujourd'hui < datelimiteCloture alors etat 'créée' , 'ouverte' ou 'Annulée'
            if ($now < $dateLimiteInscription) {
                $etatSortie[] = $etats[0]; // Créée
                $etatSortie[] = $etats[1]; // Ouverte
                $etatSortie[] = $etats[5]; // Annulée
                $sortie->setEtat($etatSortie[array_rand($etatSortie)]);
            }
            // Si datelimiteCloture < aujourd'hui < dateDebut alors etat 'En cours'
            if ($now < $dateDebut and $now > $dateLimiteInscription) {
                $sortie->setEtat($etats[3]);
            }
            // Si aujourd'hui > dateDebut 'Passées'
            if ($now > $dateDebut) {
                $sortie->setEtat($etats[4]);
            }

            /** @var Participant */
            $organisateur = $participants[array_rand($participants)];
            $sortie->setOrganisateur($organisateur);
            $sortie->setCampus($organisateur->getCampus());


            if ($sortie->getEtat() !== $etats[0]) {
                for ($j = 0; $j < $places; $j++) {
                    $sortie->addParticipant($participants[array_rand($participants)]);
                }
            }
            // Si aujourd'hui > datecloture ou nombre max de participant atteind alors etat 'Cloturée'
            if ($now > $dateLimiteInscription || count($sortie->getParticipants()) == $sortie->getNbInscriptionsMax()) {
                $sortie->setEtat($etats[2]);
            }

            $manager->persist($sortie);
        }

        $manager->flush();
    }
}
