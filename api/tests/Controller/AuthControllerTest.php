<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Parcours complet : inscription, connexion, puis accès à une route protégée.
 * La base de test est créée par "doctrine:migrations:migrate --env=test".
 */
class AuthControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testRegisterLoginAndAccessDashboard(): void
    {
        $email = uniqid('joueur_').'@example.com';

        $this->postJson('/api/register', [
            'name' => uniqid('Joueur'),
            'email' => $email,
            'password' => 'MotDePasse123!',
        ]);
        $this->assertResponseStatusCodeSame(201);

        $this->postJson('/api/login_check', ['email' => $email, 'password' => 'MotDePasse123!']);
        $this->assertResponseIsSuccessful();
        $token = $this->responseData()['token'] ?? null;
        $this->assertNotEmpty($token);

        $this->client->request('GET', '/api/dashboard', server: ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);
        $this->assertResponseIsSuccessful();
        $this->assertSame('E', $this->responseData()['rank']);
    }

    public function testRegisterRejectsMissingFields(): void
    {
        $this->postJson('/api/register', ['email' => 'incomplet@example.com']);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testLoginRejectsWrongPassword(): void
    {
        $this->postJson('/api/login_check', ['email' => 'personne@example.com', 'password' => 'faux']);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDashboardRequiresAToken(): void
    {
        $this->client->request('GET', '/api/dashboard');

        $this->assertResponseStatusCodeSame(401);
    }

    private function postJson(string $uri, array $data): void
    {
        $this->client->request('POST', $uri, server: ['CONTENT_TYPE' => 'application/json'], content: json_encode($data));
    }

    private function responseData(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }
}
