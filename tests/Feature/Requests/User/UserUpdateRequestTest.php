<?php

namespace Tests\Feature\Requests\User;

use App\Http\Requests\User\UserUpdateRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @internal
 */
class UserUpdateRequestTest extends TestCase
{
    #[Test]
    public function itShouldFailWhenUpdatingWithNonexistentDocumentId()
    {
        $data = [
            'name' => 'Murilo',
            'surname' => 'Figueiredo',
            'documents' => [
                ['id' => -1],
            ],
        ];

        $validator = Validator::make($data, (new UserUpdateRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('documents.0.id', $validator->errors()->toArray());
    }

    #[Test]
    public function itShouldFailWhenCreatingDocumentWithInvalidType()
    {
        $data = [
            'name' => 'Murilo',
            'surname' => 'Figueiredo',
            'documents' => [
                [
                    'number' => '12345678900',
                    'type' => 1234, // tipo inexistente
                ],
            ],
        ];

        $validator = Validator::make($data, (new UserUpdateRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('documents.0.type', $validator->errors()->toArray());
    }

    #[Test]
    public function itShouldPassWithValidStructureButInvalidReferences()
    {
        $data = [
            'name' => 'Murilo',
            'surname' => 'Figueiredo',
            'documents' => [
                [
                    'number' => '12345678900',
                    'type' => 9999, // tipo inexistente
                ],
                [
                    'id' => 8888, // também inexistente
                ],
            ],
        ];

        $validator = Validator::make($data, (new UserUpdateRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('documents.0.type', $validator->errors()->toArray());
        $this->assertArrayHasKey('documents.1.id', $validator->errors()->toArray());
    }

    #[Test]
    public function itShouldFailWhenDocumentsIsMissing()
    {
        $data = [
            'name' => 'Murilo',
            'surname' => 'Figueiredo',
        ];

        $validator = Validator::make($data, (new UserUpdateRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('documents', $validator->errors()->toArray());
    }

    #[Test]
    public function itShouldFailWhenDocumentIsEmpty()
    {
        $data = [
            'name' => 'Murilo',
            'surname' => 'Figueiredo',
            'documents' => [],
        ];

        $validator = Validator::make($data, (new UserUpdateRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('documents', $validator->errors()->toArray());
    }

    #[Test]
    public function itShouldFailWhenCreatingDocumentWithoutRequiredFields()
    {
        $data = [
            'name' => 'Murilo',
            'surname' => 'Figueiredo',
            'documents' => [
                [],
            ],
        ];

        $validator = Validator::make($data, (new UserUpdateRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('documents.0.number', $validator->errors()->toArray());
        $this->assertArrayHasKey('documents.0.type', $validator->errors()->toArray());
    }
}
