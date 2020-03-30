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

      $user = $this->getUser();

      return $this->render('star/index.html.twig', [
        'controller_name' => 'StarController',
        'videoFiles' => $videoFiles,
        'user' => $user,
      ]);
    }

    /**
     * @Route("/star/video/{videoId}", name="star-video")
     * @param int $videoId
     */
    public function starVideo($videoId)
    {
      $videoFiles = $this->retrieveVideoNames();

      $videoTitle = "";
      if (array_key_exists($videoId, $videoFiles)) {
        $videoTitle = $videoFiles[$videoId];
      }

      $user = $this->getUser();

      return $this->render('star/video.html.twig', [
        'controller_name' => 'StarController',
        'videoTitle' => $videoTitle,
        'videoId' => $videoId,
        'user' => $user,
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
          while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            if (count($data) > 1) {
              $videoFiles[$data[0]] = $data[1];
            }
          }
          fclose($handle);
      }

      return $videoFiles;
    }

//    private function retrieveVideoNamesOld()
//    {
//      $videoPath = "./videos"; // SG1_SEASON1_DISC2_SKU1_Title2.mp4
//      $videoFiles = scandir($videoPath);
//
//      $videoCount = count($videoFiles);
//      for ($i=0; $i < $videoCount; $i++) {
//        $videoFiles[$i] = basename($videoFiles[$i], ".mp4");
//      }
//
//      return $videoFiles;
//    }
}
