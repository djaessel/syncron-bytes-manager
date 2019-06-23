<?php

namespace App\Helper;

use App\Entity\TransferData;
use App\Entity\User;
use App\Entity\UserActivation;
use App\Repository\UserRepository;
use Psr\Container\ContainerInterface;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
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
    public function checkUserJsonData(array $jsonData)
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
    public function addNewUser(UserPasswordEncoderInterface $encoder, array $jsonData)
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
        $activationCode = $this->generateActivationCode();

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
    public function findUserByEmail(string $email)
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

    /**
     * @param string $activationCode
     * @param string $email
     * @param string|null $name
     * @return bool
     */
    public function sendUserActivationEmail(string $activationCode, string $email, $name = null)
    {
        $emailBody = "Activation Code: " . $activationCode; // just for now (testing)

        /** @var Swift_Message $message */
        $smtpTransport = new Swift_SmtpTransport('localhost', 25, null);
        $mailer = new Swift_Mailer($smtpTransport);
        $message = $mailer->createMessage();

        $message->setFrom("info@syncronbytes-mgr.ddns.net", "SyncronBytes Manager");
        $message->setReplyTo("no-reply@syncronbytes-mgr.ddns.net");
        $message->setReturnPath("no-reply@syncronbytes-mgr.ddns.net");

        $message->setTo($email, $name);
        $message->setSubject("SyncronBytes Manager - Account Activation");
        $message->setBody($emailBody);

        $result = $mailer->send($message, $failedRecipients);
        $success = ($result > 0 && empty($failedRecipients));

        return $success;
    }

    /**
     * @param int $maxRandom
     * @param int $x
     * @param int $y
     * @return bool|string
     */
    private function generateActivationCode($maxRandom = 65356, $x = 8, $y = 64)
    {
        $randomX = rand($x, $maxRandom);
        $randomY = rand($y, $maxRandom);

        $activationCodeData = strval($randomX . $randomY);
        $activationCodeHash = hash("md5", $activationCodeData);
        $activationCodeArray = str_split($activationCodeHash, 4);

        $activationCode = "";
        for ($i = 0; $i < count($activationCodeArray); $i++) {
            $activationCode .= "-" . $activationCodeArray[$i];
            if ($i % 3 === 0) {
                $i++;
                $activationCode .= $activationCodeArray[$i];
            }
        }

        return substr($activationCode, 1);
    }
}
