<?php

namespace App\Controller\Rest;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
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
     * @Rest\Get("/user/register")
     */
    public function registerUser()
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $requestData = $request->getContent();

        try {
            $jsonData = json_decode($requestData, true);
            $validUser = $this->checkUserJsonData($jsonData);
            if ($validUser) {
                $this->addNewUser($jsonData);
            }
        } catch (Throwable $exception) {
            $validUser = false;
        }

        $jsonData = array(
            'success' => boolval($validUser),
        );
        $view = $this->view($jsonData, 200);
        return $view;
    }

    /**
     * @param array $jsonData
     * @return bool
     */
    private function checkUserJsonData($jsonData)
    {
        /** @var EntityManager $manager */
        $manager = $this->getDoctrine()->getManager();

        $validUser = array_key_exists("email", $jsonData);
        $validUser &= array_key_exists("pass", $jsonData);

        if ($validUser) {
            $validUser = !empty($jsonData["email"]) && !empty($jsonData["pass"]);
        }

        if ($validUser) {
            $dataObj = $manager->getRepository("App\\Entity\\User")->findOneBy(
                array(
                    'email' => $jsonData["email"],
                )
            );
            $validUser = !empty($dataObj);
        }

        return $validUser;
    }

    /**
     * @param array $jsonData
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function addNewUser($jsonData)
    {
        /** @var EntityManager $manager */
        $manager = $this->getDoctrine()->getManager();

        $newUser = new User();

        $email = $jsonData["email"];
        $newUser->setEmail($email);

        $password = $jsonData["pass"];
        $password = $this->encoder->encodePassword($newUser, $password);
        $newUser->setPassword($password);

        $newUser->setIsActive(false);

        //$newUser->setRoles(array('ROLE_USER'));

        $manager->persist($newUser);
        $manager->flush();
    }
}
