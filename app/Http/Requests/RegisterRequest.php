<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório!',
            'email.required' => 'O e-mail é obrigatório!',
            'email.email' => 'O e-mail precisa ser válido!',
            'email.unique' => 'Esse e-mail já está sendo usado!',
            'password.required' => 'A senha é obrigatória!',
            'password.min' => 'A senha precisa ter pelo menos 8 letras!',
            'password.confirmed' => 'As senhas não são iguais!',
            'password_confirmation.required' => 'A confirmação de senha é obrigatória!',
        ];
    }
}
