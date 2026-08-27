<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

class SecurityController
{
    #[Route('/api/login_check', name: 'api_login_check', methods: ['POST'])]
    public function loginCheck(): void
    {
        throw new \LogicException('Cette méthode ne devrait jamais être exécutée : json_login intercepte la requête avant.');
    }
}