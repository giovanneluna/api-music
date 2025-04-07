<?php

namespace App\Http\Requests\Suggestion;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Suggestion;

class UpdateStatusRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $rules = [
      'motivo' => 'nullable|string|max:500',
    ];

    if ($this->route('status') === Suggestion::STATUS_REJECTED) {
      $rules['motivo'] = 'required|string|max:500';
    }

    return $rules;
  }

  public function messages(): array
  {
    return [
      'motivo.required' => 'Um motivo deve ser fornecido ao rejeitar uma sugestão',
    ];
  }
}
