<?php

namespace App\Exceptions;

class ResourceNotFoundException extends ApiException
{
  public function __construct(string $resource = 'Recurso', array $errors = [])
  {
    parent::__construct(
      "$resource não encontrado",
      $errors,
      404
    );
  }
}
