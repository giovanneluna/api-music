<?php

namespace App\Exceptions;

class UnauthorizedException extends ApiException
{
  public function __construct(string $message = 'Você não tem permissão para executar esta ação', array $errors = [])
  {
    parent::__construct(
      $message,
      $errors,
      403
    );
  }
}
