<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
			'security/login.html.twig',
			array(
				'last_username' => $lastUsername,
				'error' => $error
			)
		);
    }
	
	/**
	 * @Route("/register", name="user_register")
	 */
	public function register(Request $request): Response
	{
		// create form here
		
		// $form->handleRequest($request);
		
		// handle / generate possible error messages here later
		$error = null;
		
		//if ($form->isValid()) {
		//	// save new user data
		//	
		//	// redirect to login page
		//}
		
		return $this->render(
			'security/register.html.twig',
			array(
				//'form' => $form->createView(),
				'error' => $error,
			)
		);
	}
	
	/**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        return true;
    }
}
