<?php

namespace App\Controller;

use App\Repository\DisciplineRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class DisciplineController
{
    #[Route('/api/disciplines', name: 'api_disciplines_list', methods: ['GET'])]
    public function list(DisciplineRepository $disciplineRepository): JsonResponse
    {
        $disciplines = $disciplineRepository->findAll();

        $data = [];
        foreach ($disciplines as $discipline) {
            $data[] = [
                'id' => $discipline->getId(),
                'name' => $discipline->getName(),
                'icon' => $discipline->getIcon(),
            ];
        }

        return new JsonResponse($data);
    }
}