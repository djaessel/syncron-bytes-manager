<?php

namespace App\Command;

use App\Entity\TransferData;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FileValidCheckCommand extends Command
{
    protected static $defaultName = 'file:valid:check';

    /**
     * @var ObjectManager $manager
     */
    private $manager;

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
            ->setDescription('Check all files for a valid link or if they are expired')
            ->addOption(
                'userId',
                'u',
                InputOption::VALUE_OPTIONAL,
                'userId for user only file check',
                null
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // user files check
        $userId = $input->getOption('userId');
        if ($userId) {
            return $this->processUserFiles($io, $userId);
        }

        // All files check
        return $this->processAllFiles($io);
    }

    /**
     * @param SymfonyStyle $io
     * @param int $userId
     * @return bool
     */
    private function processUserFiles(SymfonyStyle $io, int $userId)
    {
        $io->comment("Only execute user " . $userId . " files");

        $user = $this->manager->getRepository('App\Entity\User')->find($userId);
        if (empty($user)) {
            $io->error("No user found with id '" . $userId . "'!");
            return false;
        }

        $io->success($userId . "'s files checked.");

        return true;
    }

    /**
     * @param SymfonyStyle $io
     * @return bool
     */
    private function processAllFiles(SymfonyStyle $io)
    {
        $invalidFiles = array();

        $dateLastValid = date_create("-1 week");

        /** @var TransferData[] $allFiles */
        $allFiles = $this->manager->getRepository('App\Entity\TransferData')->findAll();

        $io->text("Checking files ...");

        $percenter = $this->setupProgressBar($io, count($allFiles), $adder);

        foreach ($allFiles as $index => $file) {
            $fileZeroed = $file->getCreationDate()->getTimestamp() - $dateLastValid->getTimestamp();
            if ($fileZeroed <= 0) {
                $invalidFiles[] = $file;
                $io->comment("Adding " . $file->getId() . ":'" . $file->getDataInfo() . "'' to invalid list");
            }

            if (($index % $percenter) === 0) {
                $io->progressAdvance($adder);
            }
        }

        $io->progressFinish();

        $io->text("Deactivate invalid files ...");

        $countInvalidFiles = count($invalidFiles);
        if ($countInvalidFiles > 0) {
            $percenter = $this->setupProgressBar($io, $countInvalidFiles, $adder);

            foreach ($invalidFiles as $index => $file) {
                $file->setActive(false);

                if (($index % $percenter) === 0) {
                    $io->progressAdvance($adder);
                }
            }
            $this->manager->flush();

            $io->progressFinish();
        }

        $io->success('All files checked.');

        return true;
    }

    /**
     * @param SymfonyStyle $io
     * @param int $count
     * @param int|null $adder
     * @return int
     */
    private function setupProgressBar(SymfonyStyle $io, int $count, ?int &$adder)
    {
        $percenter = $count / 100;
        $adder = (int)(1 / $percenter);
        if ($adder <= 0) {
            $adder++; // = 1
        }

        if ($percenter < 1) {
            $percenter = 1;
        }

        $io->comment("Files: " . $count . PHP_EOL . "Itemstep: " . $percenter . PHP_EOL . "Stepcount: " . $adder);

        $io->progressStart(100);

        return (int)$percenter;
    }
}
