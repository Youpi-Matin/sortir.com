<?php

namespace App\Service;

use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:sortie:terminer',
    description: 'Ferme les sorties dont la date et l\'heure de début sont passées.'
)]
class TermineSortieCommand extends Command
{
    protected static $defaultDescription = 'Positionne les sorties En cours à \'Passée\'.';

    public function __construct(private SortieManager $manager)
    {
        parent::__construct();
    }


    /**Traitement de l'activation des sorties
     * @throws ORMException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Fermeture des sorties',
            '=====================',
            '',
        ]);

        $sorties = $this->manager->findSortiesATerminer();
        if (count($sorties) > 0) {
            $output->writeln([
                count($sorties) . ' sorties trouvées pour fermeture',
            ]);
            foreach ($sorties as $sortie) {
                $output->writeln([
                    '===================',
                    'Fermeture de:',
                    'Id:' . $sortie->getId(),
                    'Nom: ' . $sortie->getNom(),
                    'Date: ' . date_format($sortie->getDateLimiteInscription(), 'Y-m-d'),
                    'Statut: ' . $sortie->getEtat()->getLibelle(),
                    '===================',
                ]);
                $this->manager->termineSortie($sortie);
            }
        } else {
            $output->writeln('Aucune sortie trouvée pour fermeture');
        }
        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }
}
