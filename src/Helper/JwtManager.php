<?php

namespace App\Helper;

use App\Entity\User;
use App\Entity\UserActivation;
use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Psr\Container\ContainerInterface;
use Throwable;

class JwtManager
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * JwtAuthenticator constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
     * @return string
     */
    public function createToken(User $user)
    {
        $encoder = $this->container->get('lexik_jwt_authentication.jwt_manager');
        $token = $encoder->create($user);

        return $token;
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

        if (!empty($userActivation)) {
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
        $user->setIsActive(true);

        try {
            $manager = $this->container->get('doctrine')->getManager();
            $manager->persist($user);
            $manager->remove($userActivation);
            $manager->flush();

            $success = true;
        } catch (Throwable $exception) {
            $success = false;
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
            /** @var JWTEncoderInterface $jwtEncoder */
            $jwtEncoder = $this->container->get('lexik_jwt_authentication.encoder');
            $tokenData = $jwtEncoder->decode($securityJwtToken);
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
        $user = $userHelper->findUserByEmail($decodedTokenData["email"]);

        return $user;
    }
}
