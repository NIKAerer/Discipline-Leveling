<?php

namespace App\Service;

/**
 * Shared XP -> rank logic, used for both a discipline's own rank
 * (DisciplineTracking) and the user's global rank (User).
 */
class RankCalculator
{
    /**
     * XP required to reach each rank. A rank is "reached" once total XP
     * meets its threshold.
     */
    private const THRESHOLDS = [
        'E' => 0,
        'D' => 100,
        'C' => 300,
        'B' => 600,
        'A' => 1000,
        'S' => 1500,
    ];

    public function rankForExp(int $exp): string
    {
        $rank = 'E';

        foreach (self::THRESHOLDS as $candidateRank => $threshold) {
            if ($exp >= $threshold) {
                $rank = $candidateRank;
            }
        }

        return $rank;
    }

    /**
     * Progress toward the next rank, as a percentage (0-100). Returns 100
     * once the max rank (S) is reached — there is no "next" rank to climb to.
     */
    public function progressPercent(int $exp): int
    {
        $ranks = array_keys(self::THRESHOLDS);
        $currentRank = $this->rankForExp($exp);
        $currentIndex = array_search($currentRank, $ranks, true);

        if ($currentIndex === count($ranks) - 1) {
            return 100;
        }

        $currentThreshold = self::THRESHOLDS[$ranks[$currentIndex]];
        $nextThreshold = self::THRESHOLDS[$ranks[$currentIndex + 1]];
        $span = $nextThreshold - $currentThreshold;

        if ($span <= 0) {
            return 100;
        }

        $percent = (int) floor((($exp - $currentThreshold) / $span) * 100);

        return max(0, min(100, $percent));
    }
}
