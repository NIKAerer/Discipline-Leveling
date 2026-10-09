<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Base des tests fonctionnels de l'API : client HTTP, requêtes JSON
 * et création d'un utilisateur connecté.
 * La base de test est créée par "composer test" (migrations sur SQLite).
 */
abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    private ?string $token = null;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Inscrit un nouvel utilisateur, le connecte, et utilise son token
     * pour toutes les requêtes suivantes du test.
     */
    protected function loginAsNewUser(): string
    {
        $email = uniqid('joueur_').'@example.com';

        $this->requestJson('POST', '/api/register', ['name' => uniqid('Joueur'), 'email' => $email, 'password' => 'MotDePasse123!']);
        $this->assertResponseStatusCodeSame(201);

        $this->requestJson('POST', '/api/login_check', ['email' => $email, 'password' => 'MotDePasse123!']);
        $this->assertResponseIsSuccessful();
        $this->token = $this->responseData()['token'];

        return $email;
    }

    protected function useToken(string $token): void
    {
        $this->token = $token;
    }

    protected function requestJson(string $method, string $uri, ?array $data = null, ?string $rawBody = null): void
    {
        $server = ['CONTENT_TYPE' => 'application/json'];

        if ($this->token) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer '.$this->token;
        }

        $this->client->request($method, $uri, server: $server, content: $rawBody ?? ($data !== null ? json_encode($data) : null));
    }

    protected function responseData(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }
}
