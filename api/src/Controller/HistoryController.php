<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ActivityRepository;
use App\Service\StreakCalculator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class HistoryController
{
    private const DAYS = 30;
    private const RECENT_LIMIT = 8;

    /**
     * Historique des 30 derniers jours, séries en cours et record,
     * et dernières quêtes validées. ?disciplineId=X limite à une discipline.
     */
    #[Route('/api/history', name: 'api_history', methods: ['GET'])]
    public function show(Request $request, ActivityRepository $activityRepository, StreakCalculator $streakCalculator, #[CurrentUser] User $user): JsonResponse
    {
        $disciplineId = $request->query->has('disciplineId') ? $request->query->getInt('disciplineId') : null;
        $activities = $activityRepository->findForUser($user, $disciplineId);
        $today = new \DateTimeImmutable('today');

        // XP et nombre de quêtes par jour, sur les 30 derniers jours (jours vides inclus)
        $days = [];
        for ($i = self::DAYS - 1; $i >= 0; $i--) {
            $date = $today->modify("-$i days")->format('Y-m-d');
            $days[$date] = ['date' => $date, 'xp' => 0, 'count' => 0];
        }

        $activeDays = [];
        foreach ($activities as $activity) {
            $date = $activity->getDate()->format('Y-m-d');

            if (isset($days[$date])) {
                $days[$date]['xp'] += $activity->getExpWon();
                $days[$date]['count']++;
            }

            // Seules les bonnes habitudes (XP positive) font avancer la série
            if ($activity->getExpWon() > 0) {
                $activeDays[] = $date;
            }
        }

        $recent = [];
        foreach (array_slice($activities, 0, self::RECENT_LIMIT) as $activity) {
            $quest = $activity->getQuest();
            $recent[] = [
                'date' => $activity->getDate()->format('Y-m-d'),
                'label' => $quest->getLabel(),
                'discipline' => $quest->getDisciplineTracking()->getDiscipline()->getName(),
                'expWon' => $activity->getExpWon(),
            ];
        }

        return new JsonResponse([
            'currentStreak' => $streakCalculator->currentStreak($activeDays, $today),
            'bestStreak' => $streakCalculator->bestStreak($activeDays),
            'days' => array_values($days),
            'recent' => $recent,
        ]);
    }
}
