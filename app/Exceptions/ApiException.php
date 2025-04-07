<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
  protected $errors;
  protected $statusCode;

  public function __construct(string $message = 'Erro na API', array $errors = [], int $statusCode = 400, Exception $previous = null)
  {
    parent::__construct($message, 0, $previous);

    $this->errors = $errors;
    $this->statusCode = $statusCode;
  }

  public function getErrors(): array
  {
    return $this->errors;
  }

  public function getStatusCode(): int
  {
    return $this->statusCode;
  }
}
