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
		$videoPath = "/../videos/SG1_SEASON1_DISC2_SKU1_Title2.mp4";
		$videoTitle = "Test";
		
        return $this->render('star/index.html.twig', [
			'controller_name' => 'StarController',
			'videoPath' => $videoPath,
			'videoTitle' => $videoTitle,
        ]);
    }
}
