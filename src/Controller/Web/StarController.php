<?php

namespace App\Controller\Web;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

      // removes filler if video next button click instead of wait
      $fillerActive = $session->get("fillerActive", false);
      if ($fillerActive) {
        $session->remove("fillerActive");
      }

      $previousId = 0; // null
      $nextId = 0; // null

      $curEpisode = $this->manager->getRepository("App\Entity\Star\Episode")->find($videoId);

      if (!empty($curEpisode)) {
        $previousId = $this->findPreviousId($videoId);
        $nextId = $this->findNextId($videoId);

        // FIXME: merge / link episodes, seasons and series via one to many in DB
        $season = $this->manager->getRepository("App\Entity\Star\Season")
          ->find($curEpisode->getSeason());
      }

      $this->initVideoLanguages($session);

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
     * @Route("/star/set-language", name="star-set-language")
     *
     * @param SessionInterface $session
     */
    public function starSetLanguage(SessionInterface $session, Request $request)
    {
      $language = $request->request->get('lang');

      $this->setVideoLanguage($session, $language);

      return new Response("success");
    }


    private function setVideoLanguage($session, $language = "0")
    {
      $videoLanguages = $session->get("videoLanguages", array());
      $oldLanguage = $session->get("videoLanguage", "de"); // later symfony?

      if (!array_key_exists($language, $videoLanguages)) {
        $language = $oldLanguage;
      }

      $session->set("videoLanguage", $language);
    }

    private function initVideoLanguages($session)
    {
      $videoLanguages = $session->get("videoLanguages");

      if (empty($videoLanguages)) {
        // TODO: later check for symfony solution
        $videoLanguages = array(
          "de" => "Deutsch",
          "en" => "English",
        );
      }

      $session->set("videoLanguages", $videoLanguages);

      $this->setVideoLanguage($session);
    }
}
