<?php

namespace App\Service;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:archive-sortie', description: 'Archive les sortie passées depuis plus d\'un mois.')]
class ArchiveSortieCommand extends Command
{
    protected static $defaultDescription = 'Archive les sortie passées depuis plus d\'un mois.';

    public function __construct(private SortieManager $manager)
    {
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Archivage des sorties',
            '=====================',
            '',
        ]);

        $output->writeln($this->manager->findSortiesAArchiver());
        // this method must return an integer number with the "exit status code"
        // of the command. You can also use these constants to make code more readable

        // return this if there was no problem running the command
        // (it's equivalent to returning int(0))
        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }
}
