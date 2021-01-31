<?php

namespace App\Command;

use App\Entity\Star\Episode;
use App\Entity\Star\Season;
use App\Entity\Star\Series;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class StarImportCommand extends BaseCommand
{
    protected static $defaultName = 'start:import';

    protected $isVerbose = false;

    /**
     * FileValidCheckCommand constructor.
     * @param EntityManagerInterface $manager
     * @param string|null $name
     */
    public function __construct(EntityManagerInterface $manager, KernelInterface $kernel, string $name = null)
    {
        parent::__construct($manager, $kernel, $name);
    }

    protected function configure()
    {
        $this
            ->setDescription('import all star video related data')
            ->addArgument(
              'path',
              InputArgument::OPTIONAL,
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
        $success = false;

        $path = $this->setPathForImport($input);

        $this->isVerbose = $input->getOption("verbose");

        // database table names
        $tables = array(
          "episode",
          "season",
          "series"
        );

        if ($this->checkFilesExist($path, $tables)) {
            // remove all old data from database
            $this->truncateTables($tables, true);

            $success = true;

            // import all new table data
            $path .= "/";
            foreach ($tables as $key => $name) {
                $done = $this->importCsvData($path, $name, $output);
                if (!$done) {
                    $success = false;
                    $output->writeln("ERROR with ".$key.":".$name);
                }
            }
        }

        if ($success) {
            $output->writeln("success");
        } else {
            $output->writeln("failed");
        }
    }

    /**
     *
     */
    private function setPathForImport($input)
    {
        $importPath = $input->getArgument('path');

        if (empty($importPath))
        {
            $projectRoot = $this->kernel->getProjectDir();
            $importPath = $projectRoot . "/_tools";
        }

        return rtrim($importPath, "/");
    }

    /**
     *
     */
    private function checkFilesExist($path, $tables)
    {
        $allExist = false;

        if (is_dir($path)) {
            $allExist = true;
            $path .= "/";

            foreach ($tables as $name) {
                $fileName = $path.$name.".csv";
                if (!file_exists($fileName)) {
                    $allExist = false;
                }
            }
        }

        return $allExist;
    }

    /**
     *
     */
    private function readAllCsvDataFromFile($csvDataPath, $maxCharsOnLine = 1000)
    {
        $csvData = array();

        if (($handle = fopen($csvDataPath, "r")) !== FALSE) {
            $titles = fgetcsv($handle, $maxCharsOnLine, ";"); // read title row

            while (($data = fgetcsv($handle, $maxCharsOnLine, ";")) !== FALSE) {
              if (count($data) > 1) {
                // to ignore possible comments in file
                $pos = strpos($data[0], '#');
                if ($pos !== 0) {
                  $csvData[$data[0]] = $data;
                }
              }
            }

            fclose($handle);
        }

        return $csvData;
    }

    /**
     *
     */
    private function importCsvData($path, $name, $output)
    {
        $success = false;

        try {
            $pathX = $path.$name.".csv";
            $csvData = $this->readAllCsvDataFromFile($pathX);

            foreach ($csvData as $key => $data) {
                if ($this->isVerbose) {
                  $output->writeln("Processing data: " . $name . "_" . $key);
                }
                $this->handleDataByName($name, $data);
            }

            $this->manager->flush();

            $success = true;
        } catch (\Throwable $e) {
            $output->writeln($e->getMessage() . " : " . $e->getLine());
        }

        return $success;
    }

    /**
     *
     */
    private function handleDataByName($name, $data)
    {
        $found = true;

        switch ($name) {
          case 'series':
            $this->addSeries($data);
            break;
          case 'season':
            $this->addSeason($data);
            break;
          case 'episode':
            $this->addEpisode($data);
            break;
          default:
            // TODO: log error / warning
            $found = false;
            break;
        }

        return $found;
    }

    /**
     *
     */
    private function addSeries($data)
    {
        $series = new Series();
        $series->setNumber($data[1]);
        $series->setColor($data[2]);
        $series->setTitle($data[3]);

        $this->manager->persist($series);
    }

    /**
     *
     */
    private function addSeason($data)
    {
        $season = new Season();
        $season->setNumber($data[1]);
        $season->setSeries($data[2]);
        $season->setTitle($data[3]);
        $season->setStartYear($data[4]);
        $season->setEndYear($data[5]);

        $this->manager->persist($season);
    }

    /**
     *
     */
    private function addEpisode($data)
    {
        $episode = new Episode();
        $episode->setPath($data[0]);
        $episode->setTitle($data[1]." / ".$data[2]);
        $episode->setSeason($data[3]);
        $episode->setNumber($data[4]);
        $episode->setNumberAll($data[5]);
        $episode->setIsExtra($data[6] == 1);

        $this->manager->persist($episode);
    }
}
