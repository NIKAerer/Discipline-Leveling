<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

/**
 * Un body mal formé doit donner une erreur 400 lisible, jamais une erreur 500.
 */
class InvalidPayloadTest extends ApiTestCase
{
    public function testInvalidJsonReturns400(): void
    {
        $this->requestJson('POST', '/api/register', rawBody: '{pas du json');

        $this->assertResponseStatusCodeSame(400);
        $this->assertSame('The request body must be a JSON object', $this->responseData()['error']);
    }

    public function testEmptyBodyOnAuthenticatedRouteReturns400(): void
    {
        $this->loginAsNewUser();

        $this->requestJson('PATCH', '/api/profile');

        $this->assertResponseStatusCodeSame(400);
    }

    public function testWrongFieldTypeReturns400(): void
    {
        $this->requestJson('POST', '/api/register', ['name' => ['tableau'], 'email' => 'type@example.com', 'password' => 'MotDePasse123!']);

        $this->assertResponseStatusCodeSame(400);
        $this->assertSame('The "name" field must be a string', $this->responseData()['error']);
    }
}
