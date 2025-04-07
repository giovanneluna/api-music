<?php

namespace App\Exceptions;

class ValidationApiException extends ApiException
{
  public function __construct(array $errors = [], string $message = 'Erro de validação')
  {
    parent::__construct(
      $message,
      $errors,
      422
    );
  }
}
