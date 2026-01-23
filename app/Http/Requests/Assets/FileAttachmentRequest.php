<?php

namespace App\Http\Requests\Assets;

use Illuminate\Foundation\Http\FormRequest;

class FileAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $mimes = 'jpg,jpeg,png,gif,webp,svg,pdf';

        return [
            'file' => "nullable|file|mimes:{$mimes}|max:2048|required_without:files",
            'files' => 'nullable|array|required_without:file',
            'files.*' => "file|mimes:{$mimes}|max:2048",
            'description' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ];
    }
}
