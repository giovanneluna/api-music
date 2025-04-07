<?php

namespace App\Http\Requests\Music;

use Illuminate\Foundation\Http\FormRequest;

class CreateMusicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->has('youtube_id') && !$this->has('title') && !$this->has('views') && !$this->has('thumbnail')) {
            return [
                'youtube_id' => 'required|string',
            ];
        }

        return [
            'title' => 'required|string|max:255',
            'youtube_id' => 'required|string',
            'views' => 'required|integer|min:0',
            'likes' => 'nullable|integer|min:0',
            'thumbnail' => 'required|url',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'youtube_id.required' => 'O ID do YouTube é obrigatório.',
            'views.required' => 'O campo visualizações é obrigatório.',
            'thumbnail.required' => 'A URL da thumbnail é obrigatória.',
            'thumbnail.url' => 'A URL da thumbnail deve ser válida.',
        ];
    }
}
