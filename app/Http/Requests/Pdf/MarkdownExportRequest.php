<?php

namespace App\Http\Requests\Pdf;

use Illuminate\Foundation\Http\FormRequest;

class MarkdownExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Conteúdo markdown — aceito tanto como string quanto como arquivo
            'content' => 'required_without:file|nullable|string|max:2000000', // 2 MB de texto
            'file' => 'required_without:content|nullable|file|mimes:md,txt|max:4096',

            // Identificador do projeto no frontend (UUID v4)
            'project_id' => 'nullable|string|uuid|max:36',

            // Nome do arquivo gerado (sem extensão)
            'filename' => 'nullable|string|max:200',
        ];
    }
}
