<?php

namespace App\Controller\Rest;

use FOS\RestBundle\Controller\AbstractFOSRestController;
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
}
