<?php

namespace App\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class BaseCommand extends Command
{
    protected static $defaultName = 'base:command';

    /**
     * @var ObjectManager $manager
     */
    protected $manager;

    /**
     * @var KernelInterface $kernel
     */
    protected $kernel;

    /**
     * FileValidCheckCommand constructor.
     * @param EntityManagerInterface $manager
     * @param string|null $name
     */
    public function __construct(EntityManagerInterface $manager, KernelInterface $kernel, string $name = null)
    {
        $this->manager = $manager;
        $this->kernel = $kernel;

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
