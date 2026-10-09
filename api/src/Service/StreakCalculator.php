<?php

namespace App\Service;

/**
 * Calcule les séries (streaks) de jours consécutifs avec au moins une
 * bonne habitude validée.
 */
class StreakCalculator
{
    /**
     * Série en cours. Si rien n'est encore validé aujourd'hui, la série
     * d'hier compte toujours : la journée n'est pas finie.
     *
     * @param string[] $activeDays jours actifs au format Y-m-d
     */
    public function currentStreak(array $activeDays, \DateTimeImmutable $today): int
    {
        $active = array_flip($activeDays);
        $day = $today->setTime(0, 0);

        if (!isset($active[$day->format('Y-m-d')])) {
            $day = $day->modify('-1 day');
        }

        $streak = 0;
        while (isset($active[$day->format('Y-m-d')])) {
            $streak++;
            $day = $day->modify('-1 day');
        }

        return $streak;
    }

    /**
     * Plus longue série de jours consécutifs.
     *
     * @param string[] $activeDays jours actifs au format Y-m-d
     */
    public function bestStreak(array $activeDays): int
    {
        $days = array_unique($activeDays);
        sort($days);

        $best = 0;
        $current = 0;
        $previous = null;

        foreach ($days as $day) {
            $date = new \DateTimeImmutable($day);
            $current = ($previous && $previous->modify('+1 day') == $date) ? $current + 1 : 1;
            $best = max($best, $current);
            $previous = $date;
        }

        return $best;
    }
}
