<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class StarController extends AbstractController
{
    /**
     * @Route("/star", name="star")
     */
    public function index()
    {
      $videoFiles = retrieveVideoNames();

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
      $videoFiles = retrieveVideoNames();

      $videoTitle = "";
      if (array_key_exists($videoFiles, $videoId)) {
        $videoTitle = $videoFiles[$videoId];
      }

      return $this->render('star/video.html.twig', [
        'controller_name' => 'StarController',
        'videoTitle' => $videoTitle,
        'videoId' => $videoId,
      ]);
    }

    private function retrieveVideoNames()
    {
      $projectRoot = $this->get('kernel')->getProjectDir();
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

    private function retrieveVideoNamesOld()
    {
      $videoPath = "./videos"; // SG1_SEASON1_DISC2_SKU1_Title2.mp4
      $videoFiles = scandir($videoPath);

      $videoCount = count($videoFiles);
      for ($i=0; $i < $videoCount; $i++) {
        $videoFiles[$i] = basename($videoFiles[$i], ".mp4");
      }

      return $videoFiles;
    }
}
