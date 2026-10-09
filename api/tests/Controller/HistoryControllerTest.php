<?php

namespace App\Tests\Controller;

use App\Entity\Discipline;
use App\Tests\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;

class HistoryControllerTest extends ApiTestCase
{
    public function testHistoryReflectsTodaysValidatedQuest(): void
    {
        $discipline = new Discipline();
        $discipline->setName(uniqid('Discipline'));
        $discipline->setIcon('⭐');
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->persist($discipline);
        $em->flush();
        $disciplineId = $discipline->getId();

        $this->loginAsNewUser();
        $this->requestJson('POST', '/api/character', ['disciplines' => [['disciplineId' => $disciplineId]]]);
        $this->requestJson('POST', "/api/character/$disciplineId/quests", ['label' => 'Lire 20 pages', 'expValue' => 15]);
        $questId = $this->responseData()['id'];
        $this->requestJson('POST', "/api/quests/$questId/validate");

        $this->requestJson('GET', "/api/history?disciplineId=$disciplineId");

        $this->assertResponseIsSuccessful();
        $history = $this->responseData();
        $this->assertSame(1, $history['currentStreak']);
        $this->assertCount(30, $history['days']);
        $this->assertSame(['date' => date('Y-m-d'), 'xp' => 15, 'count' => 1], end($history['days']));
        $this->assertSame('Lire 20 pages', $history['recent'][0]['label']);
    }

    public function testHistoryIsEmptyForANewUser(): void
    {
        $this->loginAsNewUser();

        $this->requestJson('GET', '/api/history');

        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $this->responseData()['currentStreak']);
        $this->assertSame([], $this->responseData()['recent']);
    }
}
