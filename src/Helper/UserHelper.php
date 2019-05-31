<?php

namespace App\Helper;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserHelper
{
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * UserHelper constructor.
     * @param ObjectManager $manager
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
        $this->manager = $manager;
        $this->encoder = $encoder;
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
            $dataObj = $this->manager->getRepository("App\\Entity\\User")->findOneBy(
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
        $password = $this->encoder->encodePassword($newUser, $password);
        $newUser->setPassword($password);

        $newUser->setIsActive(false);

        //$newUser->setRoles(array('ROLE_USER'));

        $this->manager->persist($newUser);
        $this->manager->flush();
    }
}
