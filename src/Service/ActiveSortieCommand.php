<?php

namespace App\Service;

use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:active-sortie',
    description: 'Active les sorties dont la date et l\'heure sont actuelles.'
)]
class ActiveSortieCommand extends Command
{
    protected static $defaultDescription = 'Positionne les sorties Clôturées à \'Activité en cours\'.';

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
            'Activation des sorties',
            '=====================',
            '',
        ]);

        $sorties = $this->manager->findSortiesAActiver();
        if (count($sorties) > 0) {
            $output->writeln([
                count($sorties) . ' sorties trouvées pour activation',
            ]);
            foreach ($sorties as $sortie) {
                $output->writeln([
                    '===================',
                    'Activation de:',
                    'Id:' . $sortie->getId(),
                    'Nom: ' . $sortie->getNom(),
                    'Date: ' . date_format($sortie->getDateLimiteInscription(), 'Y-m-d'),
                    'Statut: ' . $sortie->getEtat()->getLibelle(),
                    '===================',
                ]);
                $this->manager->activeSortie($sortie);
            }
        } else {
            $output->writeln('Aucune sortie trouvée pour activation');
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
