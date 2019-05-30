<?php

namespace App\Controller\Rest;

use App\Entity\User;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
        $request = $this->get('request_stack')->getCurrentRequest();
        $requestData = $request->get('jsonData', array());
        $jsonData = json_decode($requestData, true);

        $validUser = array_key_exists("email", $jsonData);
        $validUser &= array_key_exists("pass", $jsonData);

        if ($validUser) {
            $newUser = new User();

            $email = $jsonData["email"];
            $newUser->setEmail($email);

            $password = $jsonData["pass"];
            $password = $this->encoder->encodePassword($newUser, $password);
            $newUser->setPassword($password);

            $newUser->setIsActive(false);

            //$newUser->setRoles(array('ROLE_USER'));

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($newUser);
            $manager->flush();
        }
    }
}
