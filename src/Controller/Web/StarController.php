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

    const MAX_CHAR_ON_LINE = 1000;


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
    public function index(SessionInterface $session)
    {
      $videoFiles = $this->retrieveVideoNames();
      $seasons = $this->retrieveSeasonData();

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

      $session->set("curVideoId", $videoId);
      $session->set("nextVideoId", $nextId);
      $session->set("previousVideoId", $previousId);

      $videoPathId = str_replace("-", "/", $videoId);

      $videoPath = "/videos/"; // static for now
      $audioPath = "/audios/"; // static for now

      $videoTitle = $this->retrieveVideoTitle($videoData);

      // FOR TESTING
      $tempVideoPath = $videoPath . $videoPathId . ".mp4";
      if (!file_exists($tempVideoPath)) {
        var_dump($videpTitle . " does not exist!");
        return $this->redirectToRoute("star");
      }

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
        $videoTitle .= $videoData[1] . " / ";
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
		      $titles = fgetcsv($handle, self::MAX_CHAR_ON_LINE, ";"); // read title row

          while (($data = fgetcsv($handle, self::MAX_CHAR_ON_LINE, ";")) !== FALSE) {
            if (count($data) > 1) {
              $videoFiles[$data[0]] = $data;
            }
          }

          fclose($handle);
      }

      return $videoFiles;
    }

    /**
     * List with all season data for all series
     */
    private function retrieveSeasonData()
    {
      $projectRoot = $this->kernel->getProjectDir();
      $seasonDataNamePath = $projectRoot . "/_tools/series_and_seasons.csv";

      $seasonData = array();

      if (($handle = fopen($seasonDataNamePath, "r")) !== FALSE) {
		      $titles = fgetcsv($handle, self::MAX_CHAR_ON_LINE, ";"); // read title row

          while (($data = fgetcsv($handle, self::MAX_CHAR_ON_LINE, ";")) !== FALSE) {
            if (count($data) > 1) {
              $seasonData[$data[0]] = $data;
            }
          }

          fclose($handle);
      }

      return $seasonData;
    }
}
