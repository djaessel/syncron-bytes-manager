<?php

namespace App\Helper;

use App\Entity\TransferData;
use App\Entity\User;
use App\Entity\UserActivation;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
     * @param UserPasswordEncoderInterface $encoder
     * @param array $jsonData
     * @return string
     */
    public function addNewUser($encoder, $jsonData)
    {
        $manager = $this->container->get('doctrine')->getManager();

        $newUser = new User();

        $email = $jsonData["email"];
        $newUser->setEmail($email);

        $password = $jsonData["pass"];
        $password = $encoder->encodePassword($newUser, $password);
        $newUser->setPassword($password);

        $newUser->setIsActive(false);

        //$newUser->setRoles(array('ROLE_USER'));

        $manager->persist($newUser);
        $manager->flush();

        $user = $this->findUserByEmail($jsonData["email"]);

        // TODO: generate random activation code
        $activationCode = "Ce29a-md29aa2s2324346s-KKs3Ikwm2";

        $userActivation = new UserActivation();
        $userActivation->setUser($user);
        $userActivation->setActivationCode($activationCode);

        $manager->persist($userActivation);
        $manager->flush();

        return $activationCode;
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

    /**
     * @param User $user
     * @return array
     */
    public function buildAccountInfo(User $user)
    {
        $allLinks = array();

        /** @var TransferData[] $transferData */
        $transferData = $user->getTransferData();
        foreach ($transferData as $data) {
            $link = $data->getLink();
            if (!empty($link)) {
                $allLinks[] = $link;
            }
        }

        $accountInfo = array(
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'links' => $allLinks,
            // ...
        );

        return $accountInfo;
    }

    /**
     * TODO: create settings entity for user
     *
     * @param User $user
     * @return array
     */
    public function buildAccountSettings(User $user)
    {
        $accountSettings = array(
            'active' => $user->getIsActive(),
            // ...
        );

        return $accountSettings;
    }
}
