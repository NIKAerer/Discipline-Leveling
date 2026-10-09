<?php

namespace App\Tests\Service;

use App\Service\RankCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RankCalculatorTest extends TestCase
{
    private RankCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new RankCalculator();
    }

    public static function rankProvider(): array
    {
        return [
            'aucune XP' => [0, 'E'],
            'juste avant D' => [99, 'E'],
            'pile au seuil D' => [100, 'D'],
            'rang C' => [450, 'C'],
            'rang B' => [600, 'B'],
            'rang A' => [1499, 'A'],
            'rang S' => [1500, 'S'],
            'au-delà de S' => [99999, 'S'],
        ];
    }

    #[DataProvider('rankProvider')]
    public function testRankForExp(int $exp, string $expectedRank): void
    {
        $this->assertSame($expectedRank, $this->calculator->rankForExp($exp));
    }

    public function testProgressIsZeroAtTheStartOfARank(): void
    {
        $this->assertSame(0, $this->calculator->progressPercent(100));
    }

    public function testProgressIsHalfwayBetweenTwoRanks(): void
    {
        // Rang D : de 100 à 300 XP, donc 200 XP correspond à 50 %.
        $this->assertSame(50, $this->calculator->progressPercent(200));
    }

    public function testProgressIsFullAtMaxRank(): void
    {
        $this->assertSame(100, $this->calculator->progressPercent(2000));
    }
}
