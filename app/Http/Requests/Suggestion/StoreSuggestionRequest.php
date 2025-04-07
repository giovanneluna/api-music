<?php

namespace App\Http\Requests\Suggestion;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuggestionRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'url' => 'required|url',
    ];
  }

  public function messages(): array
  {
    return [
      'url.required' => 'A URL do YouTube é obrigatória',
      'url.url' => 'Uma URL válida deve ser fornecida'
    ];
  }
}
