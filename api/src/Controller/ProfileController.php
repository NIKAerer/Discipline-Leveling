<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\DisciplineTrackingRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ProfileController
{
    #[Route('/api/profile', name: 'api_profile_show', methods: ['GET'])]
    public function show(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        return new JsonResponse([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'rank' => $user->getRank(),
            'expTotal' => $user->getExpTotal(),
            'avatar' => $user->getAvatar(),
        ]);
    }

    #[Route('/api/profile', name: 'api_profile_update', methods: ['PATCH'])]
    public function update(Request $request, EntityManagerInterface $em, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $emailChanged = false;

        if (array_key_exists('name', $data)) {
            $name = trim($data['name']);

            if ($name === '') {
                return new JsonResponse(['error' => 'Name cannot be empty'], 400);
            }

            $user->setName($name);
        }

        if (array_key_exists('email', $data)) {
            $email = strtolower(trim($data['email']));

            if ($email === '') {
                return new JsonResponse(['error' => 'Email cannot be empty'], 400);
            }

            if ($email !== $user->getEmail()) {
                $emailChanged = true;
            }

            $user->setEmail($email);
        }

        try {
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(['error' => 'This name or email is already taken'], 409);
        }

        return new JsonResponse([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'emailChanged' => $emailChanged,
        ]);
    }

    #[Route('/api/profile', name: 'api_profile_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $em, DisciplineTrackingRepository $disciplineTrackingRepository, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        // No cascade/orphanRemoval is configured between User and DisciplineTracking,
        // so the related rows (and their own Quest/Activity children) are removed
        // by hand, in dependency order, before the user itself.
        $trackings = $disciplineTrackingRepository->findBy(['user' => $user]);

        foreach ($trackings as $tracking) {
            foreach ($tracking->getQuests() as $quest) {
                foreach ($quest->getActivities() as $activity) {
                    $em->remove($activity);
                }
                $em->remove($quest);
            }

            foreach ($tracking->getLolMatches() as $lolMatch) {
                $em->remove($lolMatch);
            }

            $em->remove($tracking);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(['message' => 'Account deleted']);
    }
}
