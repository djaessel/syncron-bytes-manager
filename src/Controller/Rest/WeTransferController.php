<?php

namespace App\Controller\Rest;

use App\Entity\TransferData;
use App\Helper\JwtApiManager;
use App\Helper\UserHelper;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Throwable;

/**
 * Class WeTransferController
 * @package App\Controller\Rest
 */
class WeTransferController extends AbstractFOSRestController
{
    /**
     * @var UserPasswordEncoderInterface $encoder
     */
    private $encoder;

    /**
     * AppFixtures constructor.
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @Rest\Post("/user/register")
     * @param UserPasswordEncoderInterface $encoder
     * @return View
     */
    public function registerUser(UserPasswordEncoderInterface $encoder)
    {
        $jsonData = $this->getJsonData();

        $activationCode = null;
        $userHelper = new UserHelper($this->container);

        try {
            $validUser = $userHelper->checkUserJsonData($jsonData);
            if ($validUser) {
                $activationCode = $userHelper->addNewUser($encoder, $jsonData);
            }
        } catch (Throwable $exception) {
            $validUser = false;
        }

        if ($validUser === true) {
            // TODO: Send activation email code / link
            // FIXME: remove activation code later and replace with true or null
            return $this->view($activationCode, 200);
        }

        return $this->view(false, 400);
    }

    /**
     * @Rest\Post("user/activate")
     * @param JWTTokenManagerInterface $jwtManager
     * @return View
     */
    public function activateUser(JWTTokenManagerInterface $jwtManager)
    {
        $jsonData = $this->getJsonData();

        if (!is_array($jsonData)) {
            return $this->view(null, 400);
        }

        if (empty($jsonData["email"]) || empty($jsonData["activation_code"])) {
            return $this->view(array_keys($jsonData), 400);
        }

        $userHelper = new UserHelper($this->container);
        $user = $userHelper->findUserByEmail($jsonData["email"]);

        $jwtApiManager = new JwtApiManager($this->container);

        $activationCode = $jsonData["activation_code"];
        $userActivation = $jwtApiManager->getUserActivation($user, $activationCode);
        if (!empty($userActivation)) {
            $userActivated = $jwtApiManager->activateUser($user, $userActivation);
            if ($userActivated) {
                $token = $jwtManager->create($user);
                return $this->view(array('token' => $token), 200);
            }
        }

        return $this->view(null, 401);
    }

    /**
     * @Rest\Post("/user/add/link")
     * @return View
     */
    public function addTransferLink()
    {
        $jsonData = $this->getJsonData();

        if (empty($jsonData["json_web_token"])) {
            $view = $this->view("Invalid Token", 401);
            return $view;
        }

        $success = false;
        $token = $jsonData["json_web_token"];

        $jwtApiManager = new JwtApiManager($this->container);
        if (!$jwtApiManager->validateToken($token)) {
            return $this->view(false, 403);
        }

        $transferData = null;
        if (!empty($jsonData["transferData"])) {
            $transferData = $jsonData["transferData"];
        }

        $user = $jwtApiManager->retrieveAuthenticatedUser($token);

        if (is_array($transferData) && !empty($transferData)) {
            $newTransferData = new TransferData();
            $newTransferData->setUser($user);
            $newTransferData->setFileName($transferData["fileName"]);
            $newTransferData->setLink($transferData["link"]);
            $newTransferData->setIsUsed(false);

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($newTransferData);
            $manager->flush();

            $success = true;
        }

        $view = $this->view(false, 400);
        if ($success) {
            $view = $this->view(true, 200);
        }

        return $view;
    }

    /**
     * @return mixed
     */
    private function getJsonData()
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $requestData = $request->getContent();

        $jsonData = json_decode($requestData, true);

        return $jsonData;
    }
}
