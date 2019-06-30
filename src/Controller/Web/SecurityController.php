<?php

namespace App\Controller\Web;

use App\Helper\UserHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
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
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response|RedirectResponse
     */
	public function register(Request $request, UserPasswordEncoderInterface $encoder): Response
	{
        $success = false;

		$email = $request->request->get('email', "");
		$password = $request->request->get('password', "");
		$password2 = $request->request->get('password2', "");

		$errorMode = 0;
		if (!empty($email)) {
		    $errorMode++; // 1
		    if (strlen($password) <= 255 && strlen($password2) <= 255) {
		        $errorMode++; // 2
                if (strcmp($password, $password2) === 0) {
                    $errorMode++; // 3
                    $userHelper = new UserHelper($this->container);
                    $success = $userHelper->registerNewUser(
                        $encoder,
                        array(
                            "email" => $email,
                            "pass" => $password,
                        )
                    );
                }
            }
        }

		if ($success) {
		    return $this->redirectToRoute("login", array(
                // parameters ...
            ));
        }

		$error = null;
		$requestKeys = $request->request->keys();
		if (array_search("_csrf_token", $requestKeys) !== false) {
            $errors = $this->userRegistrationErrors();
            $error = $errors[$errorMode];
        }

		return $this->render(
			'security/register.html.twig',
			array(
			    'last_email' => $email,
				'error' => $error,
			)
		);
	}

    /**
     * @return array
     */
	private function userRegistrationErrors()
    {
        $errors = array(
            array(
                "messageKey" => "Email is invalid",
                "messageData" => array(),
            ),
            array(
                "messageKey" => "Password is invalid",
                "messageData" => array(),
            ),
            array(
                "messageKey" => "Passwords are different",
                "messageData" => array(),
            ),
            array(
                "messageKey" => "User already taken or other registration error",
                "messageData" => array(),
            ),
        );

        return $errors;
    }

	/**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        return true;
    }
}
