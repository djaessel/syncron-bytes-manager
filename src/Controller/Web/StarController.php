<?php

namespace App\Controller\Web;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class StarController extends AbstractController
{
    /**
     * @var ObjectManager $manager
     */
    private $manager;

    /**
     * @var KernelInterface $kernel
     */
    private $kernel;


    /**
     * Constructor
     *
     * @param KernelInterface $kernel
     */
    public function __construct(EntityManagerInterface $manager, KernelInterface $kernel)
    {
        $this->manager = $manager;
        $this->kernel = $kernel;
    }


    /**
     * @Route("/star", name="star")
     */
    public function index(SessionInterface $session)
    {
      // FIXME: merge / link episodes, seasons and series via one to many in DB
      $episodes = $this->manager->getRepository("App\Entity\Star\Episode")->findAll();
      $seasons = $this->manager->getRepository("App\Entity\Star\Season")->findAll();
      $series = $this->manager->getRepository("App\Entity\Star\Series")->findAll();

      return $this->render('star/index.html.twig', [
        'controller_name' => 'StarController',
        'episodes' => $episodes,
        'seasons' => $seasons,
        'series' => $series,
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
      // FIXME: merge / link episodes, seasons and series via one to many in DB
      $season = null;
      $episodes = $this->manager->getRepository("App\Entity\Star\Episode")->findAll();

      $previousId = null;
      $nextId = null;

      $curEpisode = null;
      // TODO: make with database call
      foreach ($episodes as $key => $episode) {
          if ($episode->getId() == $videoId) {
              $curEpisode = $episode;
          }
      }

      if (!empty($curEpisode)) {
        $previousId = $this->findPreviousId($episodes, $videoId);
        $nextId = $this->findNextId($episodes, $videoId);

        // FIXME: merge / link episodes, seasons and series via one to many in DB
        $season = $this->manager->getRepository("App\Entity\Star\Season")
          ->find($curEpisode->getSeason());
      }

      $session->set("curVideoId", $videoId);
      $session->set("nextVideoId", $nextId);
      $session->set("previousVideoId", $previousId);

      // FIXME: STATIC FOR NOW
      $videoPath = "/videos/";
      $audioPath = "/audios/";

      return $this->render('star/video.html.twig', [
        'controller_name' => 'StarController',
        'episode' => $curEpisode,
        'season' => $season,
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
      $nextId = $session->get("nextVideoId");
      $fillerActive = $session->get("fillerActive", false);

      if ($fillerActive) {
        $session->remove("fillerActive");
        $videoId = $session->get("nextVideoId", 0);

        if (empty($videoId)) {
          return $this->redirect($this->generateUrl('star'));
        }

        $urlVideo = $this->generateUrl('star-video', array('videoId' => $videoId));
        return $this->redirect($urlVideo);
      }

      $session->set("fillerActive", true);

      return $this->render('star/video_next.html.twig', [
        'controller_name' => 'StarController',
      ]);
    }

    /**
     * TODO: Move to repository
     */
    private function findPreviousId($episodes, $videoId)
    {
      $previousId = null;

      // TODO: check for actual IDs later

      if ($videoId > 1) {
          $previousId = $videoId - 1;
      }

      return $previousId;
    }

    /**
     * TODO: Move to repository
     */
    private function findNextId($episodes, $videoId)
    {
      $nextId = null;

      // TODO: check for actual IDs later

      $episodeCount = count($episodes);
      $latestEpisode = $episodes[$episodeCount - 1];

      if ($videoId < $latestEpisode->getId()) {
          $nextId = $videoId + 1;
      }

      return $nextId;
    }
}
