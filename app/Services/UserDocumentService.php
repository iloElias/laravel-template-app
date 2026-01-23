<?php

namespace App\Services;

use App\Factories\DocumentFactory;
use App\Models\Hr\Document;
use App\Models\Hr\DocumentType;
use App\Models\Hr\User;
use Illuminate\Support\Facades\Request;

class UserDocumentService
{
    public function store(Request $request, array $document): array
    {
        [$documentData, $documentType] = $this->sanitizeDocumentData($document);

        $document = Document::create($documentData);

        return [
            'uuid' => $document->uuid,
            'emission_date' => $document->emission_date,
            'type' => $documentType->key,
            'number' => $document->number,
        ];
    }

    public function handleList(?Request $request = null, array $documents): array
    {
        $items = [];

        $user = User::auth();
        $userDocuments = $user->documents()->get();

        $providedDocumentUuids = array_filter(array_map(function ($documentData) {
            return $documentData['uuid'] ?? null;
        }, $documents));

        foreach ($documents as $documentData) {
            [$documentData, $documentType] = $this->sanitizeDocumentData($documentData);

            if (empty($documentData['uuid'])) {
                $document = DocumentFactory::create($user, $documentData);
            } else {
                $document = Document::where('uuid', $documentData['uuid'])->first();
                if ($document && $document->user_id !== $user->id) {
                    throw new \Exception('Document not found.');
                }
                if ($document) {
                    $document->update($documentData);
                } else {
                    $document = DocumentFactory::create($user, $documentData);
                }
            }

            if (!$document->active) {
                $document->update(['user_id' => $user->id, 'active' => true, 'inactivated_at' => null]);
            }

            $items[] = [
                'uuid' => $document->uuid,
                'emission_date' => $document->emission_date,
                'type' => $documentType->key,
                'number' => $document->number,
            ];
        }

        foreach ($userDocuments as $userDocument) {
            if (!in_array($userDocument->uuid, $providedDocumentUuids)) {
                if ($userDocument->active) {
                    $userDocument->update(['active' => false, 'inactivated_at' => now()]);
                }
            }
        }

        return $items;
    }

    /**
     * Replace the document type key with the corresponding document_type id.
     *
     * @return [array, DocumentType]
     *
     * @throws \Exception when the key is not found
     */
    protected function sanitizeDocumentData(array $documentData)
    {
        if (!isset($documentData['document_type'])) {
            throw new \Exception('Document type key is required.');
        }

        $docType = DocumentType::where('key', $documentData['document_type'])->first();

        $documentData['document_type'] = $docType->id;

        return [$documentData, $docType];
    }
}
