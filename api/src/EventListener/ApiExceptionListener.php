<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Transforme les erreurs HTTP levées sous /api (400, 404, 405...) en JSON
 * au même format que les contrôleurs : {"error": "..."}.
 * Le front peut ainsi toujours lire le message avec extractErrorMessage().
 */
#[AsEventListener]
final class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof HttpExceptionInterface || !str_starts_with($event->getRequest()->getPathInfo(), '/api')) {
            return;
        }

        $event->setResponse(new JsonResponse(
            ['error' => $exception->getMessage()],
            $exception->getStatusCode(),
            $exception->getHeaders(),
        ));
    }
}
