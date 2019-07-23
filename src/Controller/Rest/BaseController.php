<?php

namespace App\Controller\Rest;

use App\Entity\User;
use App\Helper\JwtApiManager;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class BaseController
 * @package App\Controller\Rest
 */
class BaseController extends AbstractFOSRestController
{
    /**
     * @var UserPasswordEncoderInterface $encoder
     */
    protected $encoder;

    /**
     * AppFixtures constructor.
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @return mixed
     */
    protected function getJsonData()
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $requestData = $request->getContent();

        $jsonData = json_decode($requestData, true);

        return $jsonData;
    }

    /**
     * @param JWTEncoderInterface $jwtEncoder
     * @param array $jsonData
     * @return User|Response
     */
    protected function checkForValidJwtToken(JWTEncoderInterface $jwtEncoder, $jsonData)
    {
        if (empty($jsonData["json_web_token"])) {
            return null;
        }

        $token = $jsonData["json_web_token"];
        $jwtApiHelper = new JwtApiManager($this->container, $jwtEncoder);
        $user = $jwtApiHelper->retrieveAuthenticatedUser($token);

        return $user;
    }
}
