<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\DisciplineTrackingRepository;
use App\Service\RankCalculator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DashboardController
{
    #[Route('/api/dashboard', name: 'api_dashboard', methods: ['GET'])]
    public function show(DisciplineTrackingRepository $disciplineTrackingRepository, RankCalculator $rankCalculator, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $trackings = $disciplineTrackingRepository->findBy(['user' => $user]);

        $disciplines = [];
        foreach ($trackings as $tracking) {
            $discipline = $tracking->getDiscipline();
            $disciplines[] = [
                'disciplineId' => $discipline->getId(),
                'name' => $discipline->getName(),
                'icon' => $discipline->getIcon(),
                'goal' => $tracking->getGoal(),
                'exp' => $tracking->getExp(),
                'rank' => $tracking->getRank(),
                'progressPercent' => $rankCalculator->progressPercent($tracking->getExp()),
            ];
        }

        return new JsonResponse([
            'name' => $user->getName(),
            'rank' => $user->getRank(),
            'expTotal' => $user->getExpTotal(),
            'avatar' => $user->getAvatar(),
            'progressPercent' => $rankCalculator->progressPercent($user->getExpTotal()),
            'disciplines' => $disciplines,
        ]);
    }
}
