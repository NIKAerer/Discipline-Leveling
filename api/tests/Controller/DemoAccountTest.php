<?php

namespace App\Tests\Controller;

use App\Command\SeedCommand;
use App\Tests\ApiTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Le compte de démo est partagé par tous les visiteurs : on peut jouer
 * avec ses quêtes, mais pas changer son email ni le supprimer.
 */
class DemoAccountTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $tester = new CommandTester((new Application(static::$kernel))->find('app:seed'));
        $tester->execute(['--demo' => true]);
    }

    public function testDemoAccountCanLogInButNotBeModifiedOrDeleted(): void
    {
        $this->requestJson('POST', '/api/login_check', ['email' => SeedCommand::DEMO_EMAIL, 'password' => SeedCommand::DEMO_PASSWORD]);
        $this->assertResponseIsSuccessful();
        $this->useToken($this->responseData()['token']);

        $this->requestJson('PATCH', '/api/profile', ['email' => 'pirate@example.com']);
        $this->assertResponseStatusCodeSame(403);

        $this->requestJson('DELETE', '/api/profile');
        $this->assertResponseStatusCodeSame(403);
    }
}
