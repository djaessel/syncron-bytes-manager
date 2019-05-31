<?php

namespace App\Controller\Rest;

use App\Entity\TransferData;
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

        $manager = $this->getDoctrine()->getManager();
        $userHelper = new UserHelper($manager, $this->encoder);

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
     * @Rest\Post("/link/add")
     */
    public function addLink()
    {
        $jsonData = $this->getJsonData();

        if (empty($jsonData["json_web_token"])) {
            $view = $this->view("Invalid Token", 401);
            return $view;
        }

        $success = false;
        $token = $jsonData["json_web_token"];

        $jwtManager = $this->get('lexik_jwt_authentication.jwt_manager');
        $user = $jwtManager->decode($token);
        // TODO: Check token is valid otherwise return 403

        $transferData = null;
        if (!empty($jsonData["transferData"])) {
            $transferData = $jsonData["transferData"];
        }

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
