<?php

namespace App\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Lecture sûre du corps JSON des requêtes.
 *
 * Sans ce helper, un body vide ou mal formé donnait null à json_decode(),
 * puis une erreur 500 dès qu'on lisait $data['...']. Ici, on répond 400
 * avec un message clair (voir ApiExceptionListener).
 */
final class JsonBody
{
    public static function decode(Request $request): array
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            throw new BadRequestHttpException('The request body must be a JSON object');
        }

        return $data;
    }

    /**
     * Renvoie le champ texte, sans espaces autour, ou null s'il est absent.
     */
    public static function string(array $data, string $key): ?string
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }

        if (!is_string($data[$key])) {
            throw new BadRequestHttpException(sprintf('The "%s" field must be a string', $key));
        }

        return trim($data[$key]);
    }
}
