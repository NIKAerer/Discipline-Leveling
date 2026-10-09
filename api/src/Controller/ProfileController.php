<?php

namespace App\Controller;

use App\Entity\User;
use App\Http\JsonBody;
use App\Service\AccountDeleter;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ProfileController
{
    #[Route('/api/profile', name: 'api_profile_show', methods: ['GET'])]
    public function show(#[CurrentUser] User $user): JsonResponse
    {
        return new JsonResponse([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'rank' => $user->getRank(),
            'expTotal' => $user->getExpTotal(),
            'avatar' => $user->getAvatar(),
        ]);
    }

    #[Route('/api/profile', name: 'api_profile_update', methods: ['PATCH'])]
    public function update(Request $request, EntityManagerInterface $em, #[CurrentUser] User $user): JsonResponse
    {
        $data = JsonBody::decode($request);
        $emailChanged = false;

        if (array_key_exists('name', $data)) {
            $name = JsonBody::string($data, 'name') ?? '';

            if ($name === '' || mb_strlen($name) > 50) {
                return new JsonResponse(['error' => 'Le pseudo doit faire entre 1 et 50 caractères'], 400);
            }

            $user->setName($name);
        }

        if (array_key_exists('email', $data)) {
            $email = strtolower(JsonBody::string($data, 'email') ?? '');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return new JsonResponse(['error' => 'Adresse email invalide'], 400);
            }

            if ($email !== $user->getEmail()) {
                $emailChanged = true;
            }

            $user->setEmail($email);
        }

        try {
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(['error' => 'Ce pseudo ou cet email est déjà utilisé'], 409);
        }

        return new JsonResponse([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'emailChanged' => $emailChanged,
        ]);
    }

    #[Route('/api/profile', name: 'api_profile_delete', methods: ['DELETE'])]
    public function delete(AccountDeleter $accountDeleter, #[CurrentUser] User $user): JsonResponse
    {
        $accountDeleter->delete($user);

        return new JsonResponse(['message' => 'Account deleted']);
    }
}
