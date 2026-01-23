<?php

namespace App\Http\Requests\User;

use App\Enums\UserProfileType;
use App\Utils;
use Illuminate\Foundation\Http\FormRequest;

class UserProfileTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $cases = Utils::enumValues(UserProfileType::class);

        return [
            'profile_type' => 'required|string|in:' . implode(',', $cases),
        ];
    }
}
