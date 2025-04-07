<?php

namespace App\Http\Requests\Music;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMusicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'youtube_id' => "sometimes|required|string|unique:musics,youtube_id,{$this->route('music')->id}",
            'views' => 'sometimes|required|integer|min:0',
            'likes' => 'sometimes|nullable|integer|min:0',
            'thumbnail' => 'sometimes|required|url',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'youtube_id.required' => 'O ID do YouTube é obrigatório.',
            'youtube_id.unique' => 'Este ID do YouTube já está em uso.',
            'views.required' => 'O campo visualizações é obrigatório.',
            'thumbnail.required' => 'A URL da thumbnail é obrigatória.',
            'thumbnail.url' => 'A URL da thumbnail deve ser válida.',
        ];
    }
}
