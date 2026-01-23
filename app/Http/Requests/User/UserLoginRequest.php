<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:pgsql.hr.user',
            'password' => 'required|string|min:8',
            'remember' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.exists' => __('passwords.user'),
        ];
    }
}
