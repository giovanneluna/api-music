<?php

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponseTrait;

    protected $levels = [
        //
    ];

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (Throwable $exception, $request) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return $this->handleApiException($exception);
            }
        });
    }

    private function handleApiException(Throwable $exception): JsonResponse
    {
        if ($exception instanceof ApiException) {
            return $this->errorResponse(
                $exception->getMessage(),
                $exception->getErrors(),
                $exception->getStatusCode()
            );
        }

        if ($exception instanceof ValidationException) {
            return $this->validationErrorResponse($exception);
        }

        if ($exception instanceof ModelNotFoundException) {
            return $this->notFoundResponse('O recurso solicitado não foi encontrado');
        }

        if ($exception instanceof AuthenticationException) {
            return $this->errorResponse('Não autenticado', null, 401);
        }

        if ($exception instanceof AuthorizationException) {
            return $this->forbiddenResponse('Você não tem permissão para executar esta ação');
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->notFoundResponse('A URL solicitada não foi encontrada');
        }

        if ($exception instanceof HttpException) {
            return $this->errorResponse(
                $exception->getMessage() ?: 'Erro de HTTP',
                null,
                $exception->getStatusCode()
            );
        }

        return $this->serverErrorResponse($exception);
    }
}
