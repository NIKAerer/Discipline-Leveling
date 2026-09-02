<?php

namespace App\Controller;

use App\Entity\User;
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
        $data = json_decode($request->getContent(), true);

        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            return new JsonResponse(['error' => 'Name, email and password are required'], 400);
        }

        $name = trim($data['name']);
        $email = strtolower(trim($data['email']));

        if ($name === '' || $email === '') {
            return new JsonResponse(['error' => 'Name, email and password are required'], 400);
        }

        $user = new User();
        $user->setName($name);
        $user->setEmail($email);

        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
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
