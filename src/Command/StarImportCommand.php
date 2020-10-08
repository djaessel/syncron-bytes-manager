<?php

namespace App\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class StarImportCommand extends BaseCommand
{
    protected static $defaultName = 'start:import';

    /**
     * FileValidCheckCommand constructor.
     * @param EntityManagerInterface $manager
     * @param string|null $name
     */
    public function __construct(EntityManagerInterface $manager, string $name = null)
    {
        parent::__construct($manager, $name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Check all files for a valid link or if they are expired')
            ->addArgument(
              'path',
              InputArgument::REQUIRED,
              'Path where the import files are at'
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $importPath = $input->getArgument('path');

        // import series and seasons


        // remove all old data from database
        $this->truncateTables(array("episode","season","series"), true);


    }
}
