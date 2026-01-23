<?php

namespace App\Rules;

use App\Models\Hr\Document;
use App\Models\Hr\User;
use Illuminate\Contracts\Validation\ValidationRule;

class DocumentBelongsTo implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $user = User::auth();
        $document = Document::where(['number' => $value, 'active' => true])->first();

        if ($document && $document->user_id !== $user->id) {
            $fail(__('validation.custom.document.belongs_to', [
                'attribute' => $attribute,
                'value' => $value,
            ]));
        }
    }
}
