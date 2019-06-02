<?php

namespace App\Helper;

use App\Entity\User;
use App\Repository\UserRepository;
use Psr\Container\ContainerInterface;

class UserHelper
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * UserHelper constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $jsonData
     * @return bool
     */
    public function checkUserJsonData($jsonData)
    {
        $validUser = array_key_exists("email", $jsonData);
        $validUser &= array_key_exists("pass", $jsonData);

        if ($validUser) {
            $validUser = !empty($jsonData["email"]) && !empty($jsonData["pass"]);
        }

        if ($validUser) {
            $manager = $this->container->get('doctrine')->getManager();
            $dataObj = $manager->getRepository("App\\Entity\\User")->findOneBy(
                array(
                    'email' => $jsonData["email"],
                )
            );
            $validUser = empty($dataObj); // is new user
        }

        return $validUser;
    }

    /**
     * @param array $jsonData
     */
    public function addNewUser($jsonData)
    {
        $newUser = new User();

        $email = $jsonData["email"];
        $newUser->setEmail($email);

        $password = $jsonData["pass"];
        $encoder = $this->container->get('lexik_jwt_authentication.encoder');
        $password = $encoder->encodePassword($newUser, $password);
        $newUser->setPassword($password);

        $newUser->setIsActive(false);

        //$newUser->setRoles(array('ROLE_USER'));

        $manager = $this->container->get('doctrine')->getManager();
        $manager->persist($newUser);
        $manager->flush();
    }

    /**
     * @param string $email
     * @return User|null
     */
    public function findUserByEmail($email)
    {
        $manager = $this->container->get('doctrine')->getManager();

        /** @var UserRepository $userRepo */
        $userRepo = $manager->getRepository('App\Entity\User');
        $user = $userRepo->findOneBy(array("email" => $email));

        return $user;
    }
}
