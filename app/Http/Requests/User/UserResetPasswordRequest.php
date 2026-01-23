<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:pgsql.hr.user',
        ];
    }

    public function messages(): array
    {
        return [
            'email.exists' => __('passwords.user'),
        ];
    }
}
