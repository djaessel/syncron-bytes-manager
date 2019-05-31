<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiTestCase extends WebTestCase
{
    public function testCreateToken()
    {
        $client = $this->createClient();
        $client->request(
            "POST",
            "/api/user/register",
            array(
                'jsonData' => json_encode(
                    array(
                        'email' => "testmail@syncronbytes-mgr.ddns.net",
                        'pass' => "f62Hnwjen?ujw2S2",
                    )
                )
            )
        );

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful());
    }

    public function testToken()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();
        $jwtManager = $container->get('lexik_jwt_authentication.jwt_manager');

        $user = new User();
        $user->setEmail("test@test.com");
        $user->setPassword("argon");
        $user->setIsActive(1);

        $token = $jwtManager->create($user);

        $tokenParts = explode(".", $token);
        $tokenHeader = base64_decode($tokenParts[0]);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtHeader = json_decode($tokenHeader);
        $jwtPayload = json_decode($tokenPayload);

        var_dump($jwtPayload->email);

        $this->assertTrue(!empty($userX));
    }
}