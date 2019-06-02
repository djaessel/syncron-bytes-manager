<?php

namespace App\Controller\Rest;

use App\Entity\TransferData;
use App\Helper\JwtManager;
use App\Helper\UserHelper;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
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
     */
    public function registerUser()
    {
        $jsonData = $this->getJsonData();

        $userHelper = new UserHelper($this->container);

        try {
            $validUser = $userHelper->checkUserJsonData($jsonData);
            if ($validUser) {
                $userHelper->addNewUser($jsonData);
            }
        } catch (Throwable $exception) {
            $validUser = false;
        }

        // TODO: Send activation email code / link

        $jsonData = array('success' => boolval($validUser));
        $view = $this->view($jsonData, 200);
        return $view;
    }

    /**
     * @Rest\Post("user/activate")
     */
    public function activateUser()
    {
        $jsonData = $this->getJsonData();

        if (empty($jsonData["email"]) || empty($jsonData["activation_code"])) {
            $view = $this->view("Invalid data!", 400);
            return $view;
        }

        $userHelper = new UserHelper($this->container);
        $user = $userHelper->findUserByEmail($jsonData["email"]);

        $jwtManager = new JwtManager($this->container);

        $activationCode = $jsonData["activation_code"];
        $userActivation = $jwtManager->getUserActivation($user, $activationCode);
        if (!empty($userActivation)) {
            if ($jwtManager->activateUser($user, $userActivation)) {
                $token = $jwtManager->createToken($user);
                return $this->view($token, 200);
            }
        }

        return $this->view("Activation code expired or invalid!", 401);
    }

    /**
     * @Rest\Post("/user/add/link")
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

        $jwtManager = new JwtManager($this->container);
        if (!$jwtManager->validateToken($token)) {
            return $this->view("ERROR", 403);
        }

        $transferData = null;
        if (!empty($jsonData["transferData"])) {
            $transferData = $jsonData["transferData"];
        }

        $user = $jwtManager->retrieveAuthenticatedUser($token);

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

        $jsonData = array('success' => $success);

        $view = $this->view($jsonData, 200);
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
