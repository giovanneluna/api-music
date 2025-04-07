<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

trait ApiResponseTrait
{
    protected function successResponse($data = null, string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse(string $message, $errors = null, int $code = 400): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    protected function createdResponse($data = null, string $message = 'Recurso criado com sucesso'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    protected function validationErrorResponse(ValidationException $exception): JsonResponse
    {
        return $this->errorResponse(
            'Erro de validação',
            $exception->errors(),
            422
        );
    }

    protected function notFoundResponse(string $message = 'Recurso não encontrado'): JsonResponse
    {
        return $this->errorResponse($message, null, 404);
    }

    protected function forbiddenResponse(string $message = 'Acesso negado'): JsonResponse
    {
        return $this->errorResponse($message, null, 403);
    }

    protected function serverErrorResponse(\Exception $exception = null): JsonResponse
    {
        $message = $exception ? $exception->getMessage() : 'Erro interno do servidor';

        return $this->errorResponse(
            'Erro interno do servidor',
            config('app.debug') ? ['exception' => $message] : null,
            500
        );
    }
}
