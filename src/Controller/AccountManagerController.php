<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AccountManagerController extends AbstractController
{
    /**
     * @Route("/account/manager", name="account_manager")
     */
    public function index()
    {
        return $this->render('account_manager/index.html.twig', [
            'controller_name' => 'AccountManagerController',
        ]);
    }
}
