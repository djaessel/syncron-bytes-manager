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
        $user = $this->getUser();
        if (!empty($user)) {
            $userActive = $user->getIsActive();
            if ($userActive !== true) {
                // user is not active
                return $this->redirectToRoute("user_activate");
            }
        }

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('web/base/index.html.twig', [
            'controller_name' => 'BaseController',
        ]);
    }
}
