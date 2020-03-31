<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;

class StarController extends AbstractController
{
    /**
     * @var KernelInterface $kernel
     */
    private $kernel;

    /**
     * Constructor
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel) {
      $this->kernel = $kernel;
    }

    /**
     * @Route("/star", name="star")
     */
    public function index()
    {
      $videoFiles = $this->retrieveVideoNames();

      return $this->render('star/index.html.twig', [
        'controller_name' => 'StarController',
        'videoFiles' => $videoFiles,
      ]);
    }

    /**
     * @Route("/star/video/{videoId}", name="star-video")
     * @param int $videoId
     */
    public function starVideo($videoId)
    {
      $videoFiles = $this->retrieveVideoNames();

      $videoData = "";
      if (array_key_exists($videoId, $videoFiles)) {
        $videoData = $videoFiles[$videoId];
      }

      $videoPathId = str_replace("-", "/", $videoId);
      $videoPath = "/videos/" . $videoPathId . ".mp4"; // static for now
      $audioPath = "/audios/" . $videoPathId . ".ogg"; // static for now

      return $this->render('star/video.html.twig', [
        'controller_name' => 'StarController',
        'videoData' => $videoData,
        'videoPath' => videoPath,
		'audioPath' => audioPath,
      ]);
    }

    /**
     * list with all video files by id
     */
    private function retrieveVideoNames()
    {
      $projectRoot = $this->kernel->getProjectDir();
      $videoFilesNamePath = $projectRoot . "/_tools/videoFileNames.csv";
      $videoFiles = array();

      if (($handle = fopen($videoFilesNamePath, "r")) !== FALSE) {
		  $titles = fgetcsv($handle, 1000, ";"); // read title row
          while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            if (count($data) > 1) {
              $videoFiles[$data[0]] = $data;
            }
          }
          fclose($handle);
      }
	  
      return $videoFiles;
    }
}
