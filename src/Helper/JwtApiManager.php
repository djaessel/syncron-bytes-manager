<?php

namespace App\Helper;

use App\Entity\User;
use App\Entity\UserActivation;
use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Psr\Container\ContainerInterface;
use Throwable;

class JwtApiManager
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var JWTEncoderInterface
     */
    private $jwtEncoder;

    /**
     * JwtAuthenticator constructor.
     * @param ContainerInterface $container
     * @param JWTEncoderInterface $jwtEncoder
     */
    public function __construct(ContainerInterface $container, JWTEncoderInterface $jwtEncoder)
    {
        $this->container = $container;
        $this->jwtEncoder = $jwtEncoder;
    }


    /**
     * @param $jsonWebToken
     * @return User|null
     */
    public function retrieveAuthenticatedUser($jsonWebToken)
    {
        $user = null;

        if ($this->validateToken($jsonWebToken)) {
            $tokenData = $this->retrieveTokenData($jsonWebToken);
            $user = $this->retrieveUserFromToken($tokenData);
        }

        return $user;
    }

    /**
     * @param string $jsonWebToken
     * @return bool
     */
    public function validateToken($jsonWebToken)
    {
        $validToken = false;

        $tokenData = $this->retrieveTokenData($jsonWebToken);
        if (!empty($tokenData) && is_array($tokenData)) {
            $validToken = true;
        }

        return $validToken;
    }

    /**
     * @param User $user
     * @param string $activationCode
     * @return UserActivation|bool
     */
    public function getUserActivation(User $user, $activationCode)
    {
        /** @var EntityManager $manager */
        $manager = $this->container->get('doctrine')->getManager();

        $userActivation = $manager->getRepository('App\Entity\UserActivation')
            ->findOneBy(
                array(
                    "user" => $user,
                    "activationCode" => $activationCode,
                )
            );

        if (empty($userActivation)) {
            $userActivation = false;
        }

        return $userActivation;
    }

    /**
     * @param User $user
     * @param UserActivation $userActivation
     * @return bool
     */
    public function activateUser(User $user, UserActivation $userActivation)
    {
        $success = true;

        /** @var EntityManager $manager */
        $manager = $this->container->get('doctrine')->getManager();

        try {
            $user->setIsActive(true);
            $manager->persist($user);
            $manager->flush();
        } catch (Throwable $exception) {
            $success = false;
        }

        try {
            $userActivation->setActivationCode(null);
            $manager->persist($userActivation);
            $manager->flush();
        } catch (Throwable $exception) {
            $success = false;
        }

        // try to revert changes
        // TODO: generate to auth code if null and isActive = false
        if (!$success) {
            try {
                $user->setIsActive(false);
                $manager->persist($user);
                $manager->flush();
            } catch (Throwable $exception) {
            }
        }

        return $success;
    }

    /**
     * @param string $securityJwtToken
     * @return array|bool
     */
    private function retrieveTokenData($securityJwtToken)
    {
        try {
            $tokenData = $this->jwtEncoder->decode($securityJwtToken);
        } catch (JWTDecodeFailureException $e) {
            $tokenData = false; // false on decoding error
        }

        return $tokenData;
    }

    /**
     * @param $decodedTokenData
     * @return User|null
     */
    private function retrieveUserFromToken($decodedTokenData)
    {
        $userHelper = new UserHelper($this->container);
        $user = $userHelper->findUserByEmail($decodedTokenData["username"]); // username is email

        return $user;
    }
}
