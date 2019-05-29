<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends AbstractController
{
    /**
     * @Route("/web/base", name="web_base")
     */
    public function index()
    {
        return $this->render('web/base/index.html.twig', [
            'controller_name' => 'BaseController',
        ]);
    }
}
