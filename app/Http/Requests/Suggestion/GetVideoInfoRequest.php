<?php

namespace App\Http\Requests\Suggestion;

use Illuminate\Foundation\Http\FormRequest;

class GetVideoInfoRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'youtube_url' => 'required|url',
    ];
  }

  public function messages(): array
  {
    return [
      'youtube_url.required' => 'A URL do YouTube é obrigatória',
      'youtube_url.url' => 'Uma URL válida deve ser fornecida'
    ];
  }
}
