<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

/**
 * Parcours complet : inscription, connexion, puis accès à une route protégée.
 */
class AuthControllerTest extends ApiTestCase
{
    public function testRegisterLoginAndAccessDashboard(): void
    {
        $this->loginAsNewUser();

        $this->requestJson('GET', '/api/dashboard');

        $this->assertResponseIsSuccessful();
        $this->assertSame('E', $this->responseData()['rank']);
    }

    public function testRegisterRejectsMissingFields(): void
    {
        $this->requestJson('POST', '/api/register', ['email' => 'incomplet@example.com']);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testRegisterRejectsInvalidEmail(): void
    {
        $this->requestJson('POST', '/api/register', ['name' => 'Nika', 'email' => 'pas-un-email', 'password' => 'MotDePasse123!']);

        $this->assertResponseStatusCodeSame(400);
        $this->assertSame('Adresse email invalide', $this->responseData()['error']);
    }

    public function testRegisterRejectsShortPassword(): void
    {
        $this->requestJson('POST', '/api/register', ['name' => 'Nika', 'email' => 'court@example.com', 'password' => '1234']);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testRegisterRejectsDuplicateEmail(): void
    {
        $email = $this->loginAsNewUser();

        $this->requestJson('POST', '/api/register', ['name' => uniqid('Autre'), 'email' => $email, 'password' => 'MotDePasse123!']);

        $this->assertResponseStatusCodeSame(409);
    }

    public function testLoginRejectsWrongPassword(): void
    {
        $this->requestJson('POST', '/api/login_check', ['email' => 'personne@example.com', 'password' => 'faux']);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testProtectedRoutesRequireAToken(): void
    {
        foreach (['/api/dashboard', '/api/character', '/api/profile', '/api/lol/overview'] as $uri) {
            $this->requestJson('GET', $uri);
            $this->assertResponseStatusCodeSame(401, $uri);
        }
    }

    public function testDisciplineCatalogIsPublic(): void
    {
        $this->requestJson('GET', '/api/disciplines');

        $this->assertResponseIsSuccessful();
    }
}
