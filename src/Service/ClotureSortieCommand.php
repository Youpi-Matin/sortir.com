<?php

namespace App\Service;

use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:sortie:cloturer',
    description: 'Cloture les sortie dont la date limite d\'inscription est passée.'
)]
class ClotureSortieCommand extends Command
{
    protected static $defaultDescription = 'Cloture les sortie dont la date limite d\'inscription est passée.';

    public function __construct(private SortieManager $manager)
    {
        parent::__construct();
    }


    /**Traitement de l'archivage des sorties
     * @throws ORMException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Cloture des sorties',
            '=====================',
            '',
        ]);

        $sorties = $this->manager->findSortiesACloturer();
        if (count($sorties) > 0) {
            $output->writeln([
                count($sorties) . ' sorties trouvées pour cloture',
            ]);
            foreach ($sorties as $sortie) {
                $output->writeln([
                    '===================',
                    'Cloture de:',
                    'Id:' . $sortie->getId(),
                    'Nom: ' . $sortie->getNom(),
                    'Date: ' . date_format($sortie->getDateLimiteInscription(), 'Y-m-d'),
                    'Statut: ' . $sortie->getEtat()->getLibelle(),
                    '===================',
                ]);
                $this->manager->clotureInscription($sortie);
            }
        } else {
            $output->writeln('Aucune sortie trouvée pour archivage');
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
