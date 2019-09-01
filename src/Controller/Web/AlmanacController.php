<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AlmanacController extends AbstractController
{
    /**
     * @Route("/almanac", name="almanac")
     */
    public function index()
    {
        return $this->render('almanac/index.html.twig', [
            'controller_name' => 'AlmanacController',
        ]);
    }
}
