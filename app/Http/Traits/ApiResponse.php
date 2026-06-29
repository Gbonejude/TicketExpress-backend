<?php

declare(strict_types=1);

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Réponse de succès standard.
     */
    protected function success(mixed $data = null, string $message = '', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Réponse de création (201).
     */
    protected function created(mixed $data = null, string $message = ''): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Réponse vide 204.
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Réponse d'erreur standard.
     *
     * @param  array<string, mixed>  $errors
     */
    protected function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if (! empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
