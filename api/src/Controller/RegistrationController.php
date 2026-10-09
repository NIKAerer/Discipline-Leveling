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
            return new JsonResponse(['error' => 'Pseudo, email et mot de passe obligatoires'], 400);
        }

        if (mb_strlen($name) > 50) {
            return new JsonResponse(['error' => 'Le pseudo doit faire 50 caractères maximum'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Adresse email invalide'], 400);
        }

        if (mb_strlen($password) < 8) {
            return new JsonResponse(['error' => 'Le mot de passe doit faire au moins 8 caractères'], 400);
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
            return new JsonResponse(['error' => 'Ce pseudo ou cet email est déjà utilisé'], 409);
        }

        return new JsonResponse(['id' => $user->getId(), 'name' => $user->getName()], 201);
    }
}
