<?php

namespace App\Tests;

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
}