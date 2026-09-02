<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Quest;
use App\Entity\User;
use App\Repository\ActivityRepository;
use App\Repository\DisciplineTrackingRepository;
use App\Repository\QuestRepository;
use App\Repository\QuestTemplateRepository;
use App\Service\RankCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class QuestController
{
    #[Route('/api/character/{disciplineId}/quests', name: 'api_quests_list', methods: ['GET'])]
    public function list(int $disciplineId, DisciplineTrackingRepository $disciplineTrackingRepository, ActivityRepository $activityRepository, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $tracking = $disciplineTrackingRepository->findOneBy(['user' => $user, 'discipline' => $disciplineId]);

        if (!$tracking) {
            return new JsonResponse(['error' => 'Not tracked'], 404);
        }

        $today = new \DateTimeImmutable('today');

        $data = [];
        foreach ($tracking->getQuests() as $quest) {
            $validatedToday = $activityRepository->findOneBy(['quest' => $quest, 'date' => $today]) !== null;

            $data[] = [
                'id' => $quest->getId(),
                'label' => $quest->getLabel(),
                'expValue' => $quest->getExpValue(),
                'validatedToday' => $validatedToday,
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/character/{disciplineId}/quests', name: 'api_quests_create', methods: ['POST'])]
    public function create(int $disciplineId, Request $request, EntityManagerInterface $em, DisciplineTrackingRepository $disciplineTrackingRepository, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $tracking = $disciplineTrackingRepository->findOneBy(['user' => $user, 'discipline' => $disciplineId]);

        if (!$tracking) {
            return new JsonResponse(['error' => 'Not tracked'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $label = trim($data['label'] ?? '');
        $expValue = $data['expValue'] ?? null;

        if ($label === '' || !is_numeric($expValue) || (int) $expValue === 0) {
            return new JsonResponse(['error' => 'A label and a non-zero XP value are required'], 400);
        }

        $quest = new Quest();
        $quest->setLabel($label);
        $quest->setExpValue((int) $expValue);
        $quest->setDisciplineTracking($tracking);

        $em->persist($quest);
        $em->flush();

        return new JsonResponse([
            'id' => $quest->getId(),
            'label' => $quest->getLabel(),
            'expValue' => $quest->getExpValue(),
            'validatedToday' => false,
        ], 201);
    }

    #[Route('/api/quests/{questId}', name: 'api_quests_delete', methods: ['DELETE'])]
    public function delete(int $questId, EntityManagerInterface $em, QuestRepository $questRepository, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $quest = $questRepository->find($questId);

        if (!$quest || $quest->getDisciplineTracking()->getUser() !== $user) {
            return new JsonResponse(['error' => 'Quest not found'], 404);
        }

        $em->remove($quest);
        $em->flush();

        return new JsonResponse(['message' => 'Quest deleted']);
    }

    #[Route('/api/quests/{questId}/validate', name: 'api_quests_validate', methods: ['POST'])]
    public function validate(int $questId, EntityManagerInterface $em, QuestRepository $questRepository, ActivityRepository $activityRepository, RankCalculator $rankCalculator, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $quest = $questRepository->find($questId);

        if (!$quest || $quest->getDisciplineTracking()->getUser() !== $user) {
            return new JsonResponse(['error' => 'Quest not found'], 404);
        }

        $today = new \DateTimeImmutable('today');

        if ($activityRepository->findOneBy(['quest' => $quest, 'date' => $today])) {
            return new JsonResponse(['error' => 'Already validated today'], 409);
        }

        $activity = new Activity();
        $activity->setQuest($quest);
        $activity->setDate($today);
        $activity->setExpWon($quest->getExpValue());
        $em->persist($activity);

        $tracking = $quest->getDisciplineTracking();
        $tracking->setExp(max(0, $tracking->getExp() + $quest->getExpValue()));
        $tracking->setRank($rankCalculator->rankForExp($tracking->getExp()));

        $user->setExpTotal(max(0, $user->getExpTotal() + $quest->getExpValue()));
        $user->setRank($rankCalculator->rankForExp($user->getExpTotal()));

        $em->flush();

        return new JsonResponse([
            'validatedToday' => true,
            'disciplineExp' => $tracking->getExp(),
            'disciplineRank' => $tracking->getRank(),
            'expTotal' => $user->getExpTotal(),
            'rank' => $user->getRank(),
        ]);
    }

    #[Route('/api/quests/{questId}/validate', name: 'api_quests_unvalidate', methods: ['DELETE'])]
    public function unvalidate(int $questId, EntityManagerInterface $em, QuestRepository $questRepository, ActivityRepository $activityRepository, RankCalculator $rankCalculator, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $quest = $questRepository->find($questId);

        if (!$quest || $quest->getDisciplineTracking()->getUser() !== $user) {
            return new JsonResponse(['error' => 'Quest not found'], 404);
        }

        $today = new \DateTimeImmutable('today');
        $activity = $activityRepository->findOneBy(['quest' => $quest, 'date' => $today]);

        if (!$activity) {
            return new JsonResponse(['error' => 'Not validated today'], 409);
        }

        $em->remove($activity);

        $tracking = $quest->getDisciplineTracking();
        $tracking->setExp(max(0, $tracking->getExp() - $quest->getExpValue()));
        $tracking->setRank($rankCalculator->rankForExp($tracking->getExp()));

        $user->setExpTotal(max(0, $user->getExpTotal() - $quest->getExpValue()));
        $user->setRank($rankCalculator->rankForExp($user->getExpTotal()));

        $em->flush();

        return new JsonResponse([
            'validatedToday' => false,
            'disciplineExp' => $tracking->getExp(),
            'disciplineRank' => $tracking->getRank(),
            'expTotal' => $user->getExpTotal(),
            'rank' => $user->getRank(),
        ]);
    }

    #[Route('/api/disciplines/{disciplineId}/quest-templates', name: 'api_quest_templates_list', methods: ['GET'])]
    public function templates(int $disciplineId, QuestTemplateRepository $questTemplateRepository): JsonResponse
    {
        $templates = $questTemplateRepository->findBy(['discipline' => $disciplineId]);

        $data = [];
        foreach ($templates as $template) {
            $data[] = [
                'id' => $template->getId(),
                'label' => $template->getLabel(),
                'expValue' => $template->getExpValue(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/character/{disciplineId}/quests/from-template/{templateId}', name: 'api_quests_create_from_template', methods: ['POST'])]
    public function createFromTemplate(int $disciplineId, int $templateId, EntityManagerInterface $em, DisciplineTrackingRepository $disciplineTrackingRepository, QuestTemplateRepository $questTemplateRepository, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $tracking = $disciplineTrackingRepository->findOneBy(['user' => $user, 'discipline' => $disciplineId]);

        if (!$tracking) {
            return new JsonResponse(['error' => 'Not tracked'], 404);
        }

        $template = $questTemplateRepository->find($templateId);

        if (!$template) {
            return new JsonResponse(['error' => 'Template not found'], 404);
        }

        $quest = new Quest();
        $quest->setLabel($template->getLabel());
        $quest->setExpValue($template->getExpValue());
        $quest->setDisciplineTracking($tracking);

        $em->persist($quest);
        $em->flush();

        return new JsonResponse([
            'id' => $quest->getId(),
            'label' => $quest->getLabel(),
            'expValue' => $quest->getExpValue(),
            'validatedToday' => false,
        ], 201);
    }
}
