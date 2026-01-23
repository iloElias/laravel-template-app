<?php

namespace App\Http\Responses\User;

use App\Models\Hr\Document;
use App\Models\Hr\DocumentType;

class UserDocumentDataResponse
{
    /**
     * Format the user data for the response.
     */
    public static function format(Document $document): array
    {
        $documentType = DocumentType::where('id', $document->document_type)->first();

        return [
            'uuid' => $document->uuid,
            'emission_date' => $document->emission_date,
            'document_type' => $documentType->key,
            'mask' => $documentType->mask,
            'number' => $document->number,
        ];
    }

    /**
     * Format the user data for the response with document.
     *
     * @param Document[] $documents
     */
    public static function list(array $documents): array
    {
        $items = [];
        foreach ($documents as $document) {
            $items[] = self::format($document);
        }

        return $items;
    }
}
