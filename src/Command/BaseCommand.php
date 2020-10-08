<?php

namespace App\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StarImportCommand extends Command
{
    protected static $defaultName = 'base:command';

    /**
     * @var ObjectManager $manager
     */
    protected $manager;

    /**
     * FileValidCheckCommand constructor.
     * @param EntityManagerInterface $manager
     * @param string|null $name
     */
    public function __construct(EntityManagerInterface $manager, string $name = null)
    {
        $this->manager = $manager;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Do not use base command')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // base output
    }

    /**
    * @param array $tableNames Name of the tables which will be truncated.
    * @param bool $cascade
    * @return void
    */
    public function truncateTables($tableNames = array(), $cascade = false) {
        $connection = $this->manager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');
        foreach ($tableNames as $name) {
            $connection->executeUpdate($platform->getTruncateTableSQL($name, $cascade));
        }
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1;');
    }
}
