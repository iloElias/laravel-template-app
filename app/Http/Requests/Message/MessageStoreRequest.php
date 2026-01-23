<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class MessageStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $message = 'string|max:1000';
        $answer_to = 'nullable|exists:pgsql.chat.message,uuid';

        return [
            'chat_uuid' => 'required|exists:pgsql.chat.chat,uuid',
            'answer_to' => $answer_to,
            'message' => "required_without:messages|{$message}",
            'messages' => 'nullable|array',
            'messages.*.message' => "required|{$message}",
            'messages.*.answer_to' => $answer_to,
        ];
    }
}
