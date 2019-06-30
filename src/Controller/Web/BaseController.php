<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends AbstractController
{
    /**
     * @Route("/web/base", name="web_base")
     * @return Response|RedirectResponse
     */
    public function index()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $userActive = $user->getIsActive();
        if ($userActive !== true) {
            // user is not active
            return $this->redirectToRoute("user_activate");
        }

        return $this->render('web/base/index.html.twig', [
            'controller_name' => 'BaseController',
        ]);
    }
}
