<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\DisciplineTracking;
use App\Repository\DisciplineRepository;
use App\Repository\DisciplineTrackingRepository;
use App\Service\RankCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class CharacterController
{
    #[Route('/api/character', name: 'api_character_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, DisciplineRepository $disciplineRepository, DisciplineTrackingRepository $disciplineTrackingRepository, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (array_key_exists('avatar', $data)) {
            $user->setAvatar($data['avatar']);
        }

        if (empty($data['disciplines']) || !is_array($data['disciplines'])) {
            return new JsonResponse(['error' => 'Disciplines are required'], 400);
        }

        foreach ($data['disciplines'] as $item) {
            $discipline = $disciplineRepository->find($item['disciplineId'] ?? null);

            if (!$discipline) {
                continue;
            }

            $existing = $disciplineTrackingRepository->findOneBy([
                'user' => $user,
                'discipline' => $discipline,
            ]);

            if ($existing) {
                continue;
            }

            $tracking = new DisciplineTracking();
            $tracking->setUser($user);
            $tracking->setDiscipline($discipline);
            $tracking->setGoal($item['goal'] ?? '');
            $tracking->setExp(0);
            $tracking->setRank('E');

            $em->persist($tracking);
        }

        $em->flush();

        return new JsonResponse(['message' => 'Character created'], 201);
    }

    #[Route('/api/character', name: 'api_character_list', methods: ['GET'])]
    public function list(DisciplineTrackingRepository $disciplineTrackingRepository, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $trackings = $disciplineTrackingRepository->findBy(['user' => $user]);

        $data = [];
        foreach ($trackings as $tracking) {
            $data[] = [
                'disciplineId' => $tracking->getDiscipline()->getId(),
                'goal' => $tracking->getGoal(),
                'exp' => $tracking->getExp(),
                'rank' => $tracking->getRank(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/character/{disciplineId}', name: 'api_character_detail', methods: ['GET'])]
    public function detail(int $disciplineId, DisciplineRepository $disciplineRepository, DisciplineTrackingRepository $disciplineTrackingRepository, RankCalculator $rankCalculator, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $discipline = $disciplineRepository->find($disciplineId);

        if (!$discipline) {
            return new JsonResponse(['error' => 'Discipline not found'], 404);
        }

        $tracking = $disciplineTrackingRepository->findOneBy([
            'user' => $user,
            'discipline' => $discipline,
        ]);

        if (!$tracking) {
            return new JsonResponse(['error' => 'Not tracked'], 404);
        }

        return new JsonResponse([
            'disciplineId' => $discipline->getId(),
            'name' => $discipline->getName(),
            'icon' => $discipline->getIcon(),
            'goal' => $tracking->getGoal(),
            'exp' => $tracking->getExp(),
            'rank' => $tracking->getRank(),
            'progressPercent' => $rankCalculator->progressPercent($tracking->getExp()),
        ]);
    }

    #[Route('/api/character/{disciplineId}', name: 'api_character_update', methods: ['PATCH'])]
    public function update(int $disciplineId, Request $request, EntityManagerInterface $em, DisciplineRepository $disciplineRepository, DisciplineTrackingRepository $disciplineTrackingRepository, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $discipline = $disciplineRepository->find($disciplineId);

        if (!$discipline) {
            return new JsonResponse(['error' => 'Discipline not found'], 404);
        }

        $tracking = $disciplineTrackingRepository->findOneBy([
            'user' => $user,
            'discipline' => $discipline,
        ]);

        if (!$tracking) {
            return new JsonResponse(['error' => 'Not tracked'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $tracking->setGoal($data['goal'] ?? $tracking->getGoal());

        $em->flush();

        return new JsonResponse(['message' => 'Goal updated']);
    }
}
