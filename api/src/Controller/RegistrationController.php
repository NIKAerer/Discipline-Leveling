<?php

namespace App\Controller;

use App\Entity\User;
use App\Http\JsonBody;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): JsonResponse
    {
        $data = JsonBody::decode($request);

        $name = JsonBody::string($data, 'name') ?? '';
        $email = strtolower(JsonBody::string($data, 'email') ?? '');
        $password = $data['password'] ?? '';

        if ($name === '' || $email === '' || !is_string($password) || $password === '') {
            return new JsonResponse(['error' => 'Name, email and password are required'], 400);
        }

        if (mb_strlen($name) > 50) {
            return new JsonResponse(['error' => 'Name must be 50 characters or less'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Email is not valid'], 400);
        }

        if (mb_strlen($password) < 8) {
            return new JsonResponse(['error' => 'Password must be at least 8 characters'], 400);
        }

        $user = new User();
        $user->setName($name);
        $user->setEmail($email);

        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $user->setRank('E');
        $user->setExpTotal(0);
        $user->setCreatedAt(new \DateTimeImmutable());

        try {
            $em->persist($user);
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(['error' => 'This name or email is already taken'], 409);
        }

        return new JsonResponse(['id' => $user->getId(), 'name' => $user->getName()], 201);
    }
}
