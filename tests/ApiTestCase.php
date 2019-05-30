<?php

namespace App\Tests;

use FOS\RestBundle\Tests\Functional\WebTestCase;

class ApiTestCase extends WebTestCase
{
    public function testCreateToken()
    {
        $options = array(
            'test_case' => null,
        );

        $client = $this->createClient($options);
        $client->request(
            "POST",
            "/api/user/register",
            array()
        );

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful());
    }
}