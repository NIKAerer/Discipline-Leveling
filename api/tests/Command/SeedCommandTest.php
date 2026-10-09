<?php

namespace App\Tests\Command;

use App\Command\SeedCommand;
use App\Repository\DisciplineRepository;
use App\Repository\DisciplineTrackingRepository;
use App\Repository\UserRepository;
use App\Service\AccountDeleter;
use App\Service\RankCalculator;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SeedCommandTest extends KernelTestCase
{
    public function testSeedCanRunTwiceWithoutDuplicates(): void
    {
        $tester = $this->runSeed();
        $tester->assertCommandIsSuccessful();
        $this->runSeed()->assertCommandIsSuccessful();

        $disciplines = static::getContainer()->get(DisciplineRepository::class)->findAll();
        $names = array_map(fn ($discipline) => $discipline->getName(), $disciplines);

        $this->assertCount(count(array_unique($names)), $names, 'Aucune discipline ne doit être en double');
        $this->assertContains('LoL', $names);
    }

    public function testDemoAccountHasConsistentXpAndRank(): void
    {
        $this->runSeed()->assertCommandIsSuccessful();

        $container = static::getContainer();
        $user = $container->get(UserRepository::class)->findOneBy(['email' => SeedCommand::DEMO_EMAIL]);
        $this->assertNotNull($user);

        $trackings = $container->get(DisciplineTrackingRepository::class)->findBy(['user' => $user]);
        $this->assertCount(3, $trackings);

        $sumOfDisciplines = array_sum(array_map(fn ($tracking) => $tracking->getExp(), $trackings));
        $this->assertGreaterThan(0, $user->getExpTotal());
        $this->assertSame($sumOfDisciplines, $user->getExpTotal());
        $this->assertSame((new RankCalculator())->rankForExp($user->getExpTotal()), $user->getRank());
    }

    public function testAccountDeleterRemovesTheDemoAccountAndItsData(): void
    {
        $this->runSeed()->assertCommandIsSuccessful();

        $container = static::getContainer();
        $users = $container->get(UserRepository::class);
        $user = $users->findOneBy(['email' => SeedCommand::DEMO_EMAIL]);
        $userId = $user->getId();

        $container->get(AccountDeleter::class)->delete($user);

        $this->assertNull($users->findOneBy(['email' => SeedCommand::DEMO_EMAIL]));
        $this->assertSame([], $container->get(DisciplineTrackingRepository::class)->findBy(['user' => $userId]));
    }

    private function runSeed(): CommandTester
    {
        $application = new Application(static::bootKernel());
        $tester = new CommandTester($application->find('app:seed'));
        $tester->execute(['--demo' => true]);

        return $tester;
    }
}
