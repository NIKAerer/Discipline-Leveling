<?php

namespace App\Controller;

use App\Entity\LolMatch;
use App\Entity\User;
use App\Repository\DisciplineRepository;
use App\Repository\DisciplineTrackingRepository;
use App\Repository\LolMatchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class LolController
{
    private function getLolTracking(User $user, DisciplineRepository $disciplineRepository, DisciplineTrackingRepository $disciplineTrackingRepository)
    {
        $discipline = $disciplineRepository->findOneBy(['name' => 'LoL']);

        if (!$discipline) {
            return null;
        }

        // Scoped to the currently authenticated user — a DisciplineTracking
        // belongs to exactly one User, so this can never return another
        // user's LoL history. Every route below goes through this method.
        return $disciplineTrackingRepository->findOneBy(['user' => $user, 'discipline' => $discipline]);
    }

    #[Route('/api/lol/overview', name: 'api_lol_overview', methods: ['GET'])]
    public function overview(DisciplineRepository $disciplineRepository, DisciplineTrackingRepository $disciplineTrackingRepository, LolMatchRepository $lolMatchRepository, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $tracking = $this->getLolTracking($user, $disciplineRepository, $disciplineTrackingRepository);

        if (!$tracking) {
            return new JsonResponse(['error' => 'You are not tracking the LoL discipline yet'], 404);
        }

        $matches = $lolMatchRepository->findBy(['disciplineTracking' => $tracking], ['playedAt' => 'ASC', 'id' => 'ASC']);

        $matchesData = [];
        $winsByChampion = [];
        $lossesByChampion = [];
        $totalWins = 0;
        $totalLosses = 0;

        $runningLp = $tracking->getLpStarting() ?? 0;

        foreach ($matches as $match) {
            $runningLp += $match->getLpChange();

            $matchesData[] = [
                'id' => $match->getId(),
                'playedAt' => $match->getPlayedAt()->format('c'),
                'champion' => $match->getChampion(),
                'role' => $match->getRole(),
                'matchup' => $match->getMatchup(),
                'kills' => $match->getKills(),
                'deaths' => $match->getDeaths(),
                'assists' => $match->getAssists(),
                'gameDurationMinutes' => $match->getGameDurationMinutes(),
                'cs' => $match->getCs(),
                'win' => $match->isWin(),
                'lpChange' => $match->getLpChange(),
                'cumulativeLp' => $runningLp,
            ];

            $champion = $match->getChampion();

            if ($match->isWin()) {
                $winsByChampion[$champion] = ($winsByChampion[$champion] ?? 0) + 1;
                $totalWins++;
            } else {
                $lossesByChampion[$champion] = ($lossesByChampion[$champion] ?? 0) + 1;
                $totalLosses++;
            }
        }

        $champions = array_unique(array_merge(array_keys($winsByChampion), array_keys($lossesByChampion)));
        $winrateByChampion = [];

        foreach ($champions as $champion) {
            $wins = $winsByChampion[$champion] ?? 0;
            $losses = $lossesByChampion[$champion] ?? 0;
            $total = $wins + $losses;

            $winrateByChampion[] = [
                'champion' => $champion,
                'wins' => $wins,
                'losses' => $losses,
                'total' => $total,
                'winratePercent' => $total > 0 ? (int) round(($wins / $total) * 100) : 0,
            ];
        }

        usort($winrateByChampion, fn ($a, $b) => $b['total'] <=> $a['total']);

        $hasBaseline = $tracking->getLpStarting() !== null;
        $currentLp = ($hasBaseline || $matches !== []) ? $runningLp : null;

        return new JsonResponse([
            'lpGoal' => $tracking->getLpGoal(),
            'lpStarting' => $tracking->getLpStarting(),
            'currentLp' => $currentLp,
            'matches' => $matchesData,
            'winrateByChampion' => $winrateByChampion,
            'overall' => [
                'wins' => $totalWins,
                'losses' => $totalLosses,
                'total' => $totalWins + $totalLosses,
                'winratePercent' => ($totalWins + $totalLosses) > 0 ? (int) round(($totalWins / ($totalWins + $totalLosses)) * 100) : 0,
            ],
        ]);
    }

    #[Route('/api/lol/matches', name: 'api_lol_matches_create', methods: ['POST'])]
    public function createMatch(Request $request, EntityManagerInterface $em, DisciplineRepository $disciplineRepository, DisciplineTrackingRepository $disciplineTrackingRepository, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $tracking = $this->getLolTracking($user, $disciplineRepository, $disciplineTrackingRepository);

        if (!$tracking) {
            return new JsonResponse(['error' => 'You are not tracking the LoL discipline yet'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $champion = trim($data['champion'] ?? '');
        $role = isset($data['role']) ? trim((string) $data['role']) : null;
        $matchup = isset($data['matchup']) ? trim((string) $data['matchup']) : null;
        $win = $data['win'] ?? null;
        $kills = $data['kills'] ?? null;
        $deaths = $data['deaths'] ?? null;
        $assists = $data['assists'] ?? null;
        $gameDurationMinutes = $data['gameDurationMinutes'] ?? null;
        $cs = $data['cs'] ?? null;
        $lpChange = $data['lpChange'] ?? null;

        if (
            $champion === ''
            || !is_bool($win)
            || !is_numeric($kills)
            || !is_numeric($deaths)
            || !is_numeric($assists)
            || !is_numeric($lpChange)
        ) {
            return new JsonResponse(['error' => 'Champion, result, KDA and LP change are required'], 400);
        }

        if ($gameDurationMinutes !== null && !is_numeric($gameDurationMinutes)) {
            return new JsonResponse(['error' => 'Game duration must be a number'], 400);
        }

        if ($cs !== null && !is_numeric($cs)) {
            return new JsonResponse(['error' => 'CS must be a number'], 400);
        }

        $match = new LolMatch();
        $match->setChampion($champion);
        $match->setRole($role !== '' && $role !== null ? $role : null);
        $match->setMatchup($matchup !== '' && $matchup !== null ? $matchup : null);
        $match->setKills((int) $kills);
        $match->setDeaths((int) $deaths);
        $match->setAssists((int) $assists);
        $match->setGameDurationMinutes($gameDurationMinutes !== null ? (int) $gameDurationMinutes : null);
        $match->setCs($cs !== null ? (int) $cs : null);
        $match->setWin($win);
        $match->setLpChange((int) $lpChange);
        $match->setPlayedAt(new \DateTimeImmutable());
        $match->setDisciplineTracking($tracking);

        $em->persist($match);
        $em->flush();

        return new JsonResponse([
            'id' => $match->getId(),
            'playedAt' => $match->getPlayedAt()->format('c'),
            'champion' => $match->getChampion(),
            'role' => $match->getRole(),
            'matchup' => $match->getMatchup(),
            'kills' => $match->getKills(),
            'deaths' => $match->getDeaths(),
            'assists' => $match->getAssists(),
            'gameDurationMinutes' => $match->getGameDurationMinutes(),
            'cs' => $match->getCs(),
            'win' => $match->isWin(),
            'lpChange' => $match->getLpChange(),
        ], 201);
    }

    #[Route('/api/lol/matches/{matchId}', name: 'api_lol_matches_delete', methods: ['DELETE'])]
    public function deleteMatch(int $matchId, EntityManagerInterface $em, LolMatchRepository $lolMatchRepository, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $match = $lolMatchRepository->find($matchId);

        if (!$match || $match->getDisciplineTracking()->getUser() !== $user) {
            return new JsonResponse(['error' => 'Match not found'], 404);
        }

        $em->remove($match);
        $em->flush();

        return new JsonResponse(['message' => 'Match deleted']);
    }

    #[Route('/api/lol/settings', name: 'api_lol_settings_update', methods: ['PATCH'])]
    public function updateSettings(Request $request, EntityManagerInterface $em, DisciplineRepository $disciplineRepository, DisciplineTrackingRepository $disciplineTrackingRepository, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $tracking = $this->getLolTracking($user, $disciplineRepository, $disciplineTrackingRepository);

        if (!$tracking) {
            return new JsonResponse(['error' => 'You are not tracking the LoL discipline yet'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!array_key_exists('lpGoal', $data) && !array_key_exists('lpStarting', $data)) {
            return new JsonResponse(['error' => 'lpGoal or lpStarting is required'], 400);
        }

        if (array_key_exists('lpGoal', $data)) {
            $lpGoal = $data['lpGoal'];

            if ($lpGoal !== null && !is_numeric($lpGoal)) {
                return new JsonResponse(['error' => 'lpGoal must be a number or null'], 400);
            }

            $tracking->setLpGoal($lpGoal !== null ? (int) $lpGoal : null);
        }

        if (array_key_exists('lpStarting', $data)) {
            $lpStarting = $data['lpStarting'];

            if ($lpStarting !== null && !is_numeric($lpStarting)) {
                return new JsonResponse(['error' => 'lpStarting must be a number or null'], 400);
            }

            $tracking->setLpStarting($lpStarting !== null ? (int) $lpStarting : null);
        }

        $em->flush();

        return new JsonResponse([
            'lpGoal' => $tracking->getLpGoal(),
            'lpStarting' => $tracking->getLpStarting(),
        ]);
    }
}
