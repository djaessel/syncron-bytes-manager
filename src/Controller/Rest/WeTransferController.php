<?php

namespace App\Controller\Rest;

use App\Helper\GeneralApiHelper;
use App\Helper\JwtApiManager;
use App\Helper\UserHelper;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Throwable;

/**
 * Class WeTransferController
 * @package App\Controller\Rest
 */
class WeTransferController extends BaseController
{
    /**
     * @Rest\Post("/user/register")
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function registerUser(UserPasswordEncoderInterface $encoder)
    {
        $jsonData = $this->getJsonData();

        $view = null;
        if (!is_array($jsonData)) {
            $view = $this->view(null, 400);
        }

        if (empty($jsonData["email"]) || empty($jsonData["pass"])) {
            $view = $this->view(array_keys($jsonData), 400);
        }

        if (!empty($view)) {
            return $this->handleView($view);
        }

        $userHelper = new UserHelper($this->container);

        try {
            $validUser = $userHelper->checkUserJsonData($jsonData);
            if ($validUser) {
                $activationCode = $userHelper->addNewUser($encoder, $jsonData);
            }
        } catch (Throwable $exception) {
            $validUser = false;
        }

        $view = $this->view($validUser, 400);
        if ($validUser === true && !empty($activationCode)) {
            $sentMail = $userHelper->sendUserActivationEmail($activationCode, $jsonData["email"]);
            $view = $this->view($sentMail, 200);
        }

        return $this->handleView($view);
    }

    /**
     * @Rest\Post("user/activate")
     * @param JWTTokenManagerInterface $jwtManager
     * @param JWTEncoderInterface $jwtEncoder
     * @return Response
     */
    public function activateUser(JWTTokenManagerInterface $jwtManager, JWTEncoderInterface $jwtEncoder)
    {
        $jsonData = $this->getJsonData();

        $view = null;
        if (!is_array($jsonData)) {
            $view = $this->view(null, 400);
        }

        if (empty($jsonData["email"]) || empty($jsonData["activation_code"])) {
            $view = $this->view(array_keys($jsonData), 400);
        }

        if (!empty($view)) {
            return $this->handleView($view);
        }

        $userHelper = new UserHelper($this->container);
        $user = $userHelper->findUserByEmail($jsonData["email"]);

        if (empty($user)) {
            $view = $this->view("EMAIL_ERROR", 400);
            return $this->handleView($view);
        }

        $jwtApiManager = new JwtApiManager($this->container, $jwtEncoder);

        $view = $this->view(null, 401);

        $activationCode = $jsonData["activation_code"];
        $userActivation = $jwtApiManager->getUserActivation($user, $activationCode);
        if (!empty($userActivation)) {
            $userActivated = $jwtApiManager->activateUser($user, $userActivation);
            if ($userActivated) {
                $token = $jwtManager->create($user);
                $view = $this->view(array('token' => $token), 200);
            }
        }

        return $this->handleView($view);
    }

    /**
     * @Rest\Post("/user/login")
     * @param UserPasswordEncoderInterface $encoder
     * @param JWTTokenManagerInterface $jwtManager
     * @return Response
     */
    public function loginUser(UserPasswordEncoderInterface $encoder, JWTTokenManagerInterface $jwtManager)
    {
        $jsonData = $this->getJsonData();

        $view = null;
        if (!is_array($jsonData)) {
            $view = $this->view(null, 400);
        }

        if (empty($jsonData["email"]) || empty($jsonData["pass"])) {
            $view = $this->view(array_keys($jsonData), 400);
        }

        if (!empty($view)) {
            return $this->handleView($view);
        }

        $userHelper = new UserHelper($this->container);

        $email = $jsonData["email"];
        $user = $userHelper->findUserByEmail($email);

        $view = $this->view(null, 401);

        $password = $jsonData["pass"];
        if ($encoder->isPasswordValid($user, $password)) {
            $token = $jwtManager->create($user);
            $view = $this->view(array('token' => $token), 200);
        }

        return $this->handleView($view);
    }

    /**
     * @Rest\Post("/user/add/link")
     * @param JWTEncoderInterface $jwtEncoder
     * @return Response
     */
    public function addTransferLink(JWTEncoderInterface $jwtEncoder)
    {
        $jsonData = $this->getJsonData();

        if (empty($jsonData["json_web_token"])) {
            $view = $this->view("Invalid Token", 401);
            return $this->handleView($view);
        }

        $token = $jsonData["json_web_token"];

        $jwtApiManager = new JwtApiManager($this->container, $jwtEncoder);
        if (!$jwtApiManager->validateToken($token)) {
            $view = $this->view(false, 403);
            return $this->handleView($view);
        }

        $transferData = null;
        if (!empty($jsonData["transferData"])) {
            $transferData = $jsonData["transferData"];
        }

        $user = $jwtApiManager->retrieveAuthenticatedUser($token);

        if (empty($user)) {
            $view = $this->view("Invalid Token", 401);
            return $this->handleView($view);
        }

        /** @var EntityManager $manager */
        $manager = $this->getDoctrine()->getManager();

        $generalHelper = new GeneralApiHelper();
        $generalHelper->addTransferDataIfNew($user, $transferData, $manager);

        $view = $this->view(true, 200);

        return $this->handleView($view);
    }

    /**
     * @Rest\Post("/user/account/info")
     * @param JWTEncoderInterface $jwtEncoder
     * @return Response
     */
    public function retrieveAccountInfo(JWTEncoderInterface $jwtEncoder)
    {
        $jsonData = $this->getJsonData();

        if (empty($jsonData["json_web_token"])) {
            $view = $this->view("Invalid Token", 401);
            return $this->handleView($view);
        }

        $token = $jsonData["json_web_token"];
        $jwtApiHelper = new JwtApiManager($this->container, $jwtEncoder);
        $user = $jwtApiHelper->retrieveAuthenticatedUser($token);

        if (empty($user)) {
            $view = $this->view("Invalid Token", 401);
            return $this->handleView($view);
        }

        $userHelper = new UserHelper($this->container);
        $accountInfo = $userHelper->buildAccountInfo($user);

        $jsonData = array('info' => $accountInfo);

        $view = $this->view($jsonData, 200);

        return $this->handleView($view);
    }

    /**
     * @Rest\Post("/user/account/settings")
     * @param JWTEncoderInterface $jwtEncoder
     * @return Response
     */
    public function retrieveAccountSettings(JWTEncoderInterface $jwtEncoder)
    {
        $jsonData = $this->getJsonData();

        if (empty($jsonData["json_web_token"])) {
            $view = $this->view("Invalid Token", 401);
            return $this->handleView($view);
        }

        $token = $jsonData["json_web_token"];
        $jwtApiHelper = new JwtApiManager($this->container, $jwtEncoder);
        $user = $jwtApiHelper->retrieveAuthenticatedUser($token);

        if (empty($user)) {
            $view = $this->view("Invalid Token", 401);
            return $this->handleView($view);
        }

        $userHelper = new UserHelper($this->container);
        $accountSettings = $userHelper->buildAccountSettings($user);

        $jsonData = array('settings' => $accountSettings);

        $view = $this->view($jsonData, 200);

        return $this->handleView($view);
    }
}
