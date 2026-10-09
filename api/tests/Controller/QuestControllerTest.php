<?php

namespace App\Tests\Controller;

use App\Entity\Discipline;
use App\Tests\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Cœur de l'appli : suivre une discipline, créer une quête, la valider
 * pour gagner de l'XP, et l'annuler.
 */
class QuestControllerTest extends ApiTestCase
{
    public function testValidatingAQuestGivesXpOncePerDay(): void
    {
        $disciplineId = $this->createDiscipline();
        $this->loginAsNewUser();

        $this->requestJson('POST', '/api/character', ['avatar' => 'avatar-1', 'disciplines' => [['disciplineId' => $disciplineId, 'goal' => 'Progresser']]]);
        $this->assertResponseStatusCodeSame(201);

        $this->requestJson('POST', "/api/character/$disciplineId/quests", ['label' => 'Coder 1 h', 'expValue' => 120]);
        $this->assertResponseStatusCodeSame(201);
        $questId = $this->responseData()['id'];

        $this->requestJson('POST', "/api/quests/$questId/validate");
        $this->assertResponseIsSuccessful();
        $this->assertSame(120, $this->responseData()['expTotal']);
        $this->assertSame('D', $this->responseData()['rank']);

        $this->requestJson('POST', "/api/quests/$questId/validate");
        $this->assertResponseStatusCodeSame(409, 'Une quête ne se valide qu\'une fois par jour');

        $this->requestJson('DELETE', "/api/quests/$questId/validate");
        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $this->responseData()['expTotal']);
        $this->assertSame('E', $this->responseData()['rank']);
    }

    public function testCannotValidateSomeoneElsesQuest(): void
    {
        $disciplineId = $this->createDiscipline();
        $this->loginAsNewUser();
        $this->requestJson('POST', '/api/character', ['disciplines' => [['disciplineId' => $disciplineId]]]);
        $this->requestJson('POST', "/api/character/$disciplineId/quests", ['label' => 'Ma quête', 'expValue' => 10]);
        $questId = $this->responseData()['id'];

        $this->loginAsNewUser();
        $this->requestJson('POST', "/api/quests/$questId/validate");

        $this->assertResponseStatusCodeSame(404);
    }

    private function createDiscipline(): int
    {
        $discipline = new Discipline();
        $discipline->setName(uniqid('Discipline'));
        $discipline->setIcon('⭐');

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->persist($discipline);
        $em->flush();

        return $discipline->getId();
    }
}
