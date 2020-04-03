<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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

      // TODO: use database later
      // id, season_number, series_number, title
      // FIXME: episode count and years should be separated later
      $seasons = array(
        array(1, 1, 1, "Die Reise geht weiter (1997–1998, #1–22)"),
        array(2, 2, 1, "Entdecke neue Welten (1998–1999, #23–44)"),
        array(3, 3, 1, "Galaktische Abenteuer (1999–2000, #45–66)"),
        array(4, 4, 1, "Ferne Welten, neue Gefahren (2000-2001, #67-88)"),
      );
      // TODO: use database later

      return $this->render('star/index.html.twig', [
        'controller_name' => 'StarController',
        'videoFiles' => $videoFiles,
        'seasons' => $seasons,
      ]);
    }

    /**
     * @Route("/star/video/{videoId}", name="star-video")
     *
     * @param SessionInterface $session
     * @param int $videoId
     */
    public function starVideo(SessionInterface $session, $videoId)
    {
      $videoFiles = $this->retrieveVideoNames();

      $videoData = array();
      $previousId = null;
      $nextId = null;

      if (array_key_exists($videoId, $videoFiles)) {
        $videoData = $videoFiles[$videoId];

        $previousId = $this->findPreviousId($videoFiles, $videoId);
        $nextId = $this->findNextId($videoFiles, $videoId);
      }

      $session->set("nextVideoId", $nextId);
      $session->set("previousVideoId", $previousId);

      $videoPathId = str_replace("-", "/", $videoId);
      $videoPath = "/videos/"; // static for now
      $audioPath = "/audios/"; // static for now

      $videoTitle = $this->retrieveVideoTitle($videoData);

      return $this->render('star/video.html.twig', [
        'controller_name' => 'StarController',
        'videoData' => $videoData,
        'videoPathId' => $videoPathId,
		    'videoTitle' => $videoTitle,
        'videoPath' => $videoPath,
		    'audioPath' => $audioPath,
      ]);
    }

    /**
     * @Route("/star/video-next", name="star-video-next")
     *
     * @param SessionInterface $session
     */
    public function starVideoNext(SessionInterface $session)
    {
      //$nextId = $session->get("nextVideoId");

      return $this->render('star/video_next.html.twig', [
        'controller_name' => 'StarController',
      ]);
    }

    /**
     * @param array $videoFiles
     * @param string $videoId
     */
    private function findPreviousId(array $videoFiles, string $videoId)
    {
      $previousId = null;

      $keys = array_keys($videoFiles);
      $curIndex = array_search($videoId, $keys);

      if ($curIndex > 0) {
        for ($i = $curIndex - 1; empty($previousId) && $i >= 0; $i--) {
          $xData = $videoFiles[$keys[$i]];
          if ($xData[6] == 0) {
            $previousId = $xData[0];
          }
        }
      }

      return $previousId;
    }

    /**
     * @param array $videoFiles
     * @param string $videoId
     */
    private function findNextId(array $videoFiles, string $videoId)
    {
      $nextId = null;

      $keys = array_keys($videoFiles);
      $curIndex = array_search($videoId, $keys);

      $keyCount = count($keys);
      if ($curIndex < $keyCount - 1) {
        for ($i = $curIndex + 1; empty($nextId) && $i < $keyCount; $i++) {
          $xData = $videoFiles[$keys[$i]];
          if ($xData[6] == 0) {
            $nextId = $xData[0];
          }
        }
      }

      return $nextId;
    }

    /**
  	 * Generate video title from array data
  	 *
  	 * @param array $videoData
  	 * @return string
  	 */
  	private function retrieveVideoTitle($videoData)
  	{
      $middle = " - Episode " . $videoData[4];
      if ($videoData[6] == 1) {
      	$middle = " - Extra";
      }

      // FIXME: check for language later
      $videoTitle = "Staffel " . $videoData[3] . $middle . ": ";

      // FIXME: decide which title according to language settings later
      // (e.g. en, de, both)
      if (!empty($videoData[1])) {
        $videoData[1] . " / ";
      }
      $videoTitle .= $videoData[2];

      return $videoTitle;
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
