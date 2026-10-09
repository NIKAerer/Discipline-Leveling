<?php

namespace App\Tests\Service;

use App\Service\StreakCalculator;
use PHPUnit\Framework\TestCase;

class StreakCalculatorTest extends TestCase
{
    private StreakCalculator $calculator;
    private \DateTimeImmutable $today;

    protected function setUp(): void
    {
        $this->calculator = new StreakCalculator();
        $this->today = new \DateTimeImmutable('2026-10-09');
    }

    public function testNoActivityMeansNoStreak(): void
    {
        $this->assertSame(0, $this->calculator->currentStreak([], $this->today));
        $this->assertSame(0, $this->calculator->bestStreak([]));
    }

    public function testStreakIncludingToday(): void
    {
        $days = ['2026-10-07', '2026-10-08', '2026-10-09'];

        $this->assertSame(3, $this->calculator->currentStreak($days, $this->today));
    }

    public function testStreakIsKeptWhenTodayIsNotDoneYet(): void
    {
        $days = ['2026-10-07', '2026-10-08'];

        $this->assertSame(2, $this->calculator->currentStreak($days, $this->today));
    }

    public function testStreakIsBrokenAfterAMissedDay(): void
    {
        $days = ['2026-10-05', '2026-10-06', '2026-10-07'];

        $this->assertSame(0, $this->calculator->currentStreak($days, $this->today));
    }

    public function testBestStreakFindsTheLongestRunAndIgnoresDuplicates(): void
    {
        $days = ['2026-09-01', '2026-09-02', '2026-09-02', '2026-09-03', '2026-09-10', '2026-09-11'];

        $this->assertSame(3, $this->calculator->bestStreak($days));
    }

    public function testBestStreakAcrossMonths(): void
    {
        $this->assertSame(2, $this->calculator->bestStreak(['2026-09-30', '2026-10-01']));
    }
}
