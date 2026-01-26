<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:1|max:255',
            'surname' => 'nullable|string|min:1|max:255',
            'language' => 'nullable|string|max:10',
            'pix_key' => 'nullable|string|max:255',
        ];
    }

    protected function passedValidation(): void
    {
        $this->replace([
            'name' => ucwords(strtolower($this->input('name'))),
            'surname' => ucwords(strtolower($this->input('surname'))),
        ]);
    }
}
