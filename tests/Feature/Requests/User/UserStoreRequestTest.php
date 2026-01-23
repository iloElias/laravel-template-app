<?php

namespace Tests\Feature\Requests\User;

use App\Http\Requests\User\UserStoreRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @internal
 */
class UserStoreRequestTest extends TestCase
{
    #[Test]
    public function it_should_pass_with_valid_data()
    {
        $data = $this->getBaseValidData();

        $validator = Validator::make($data, (new UserStoreRequest())->rules());
        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function it_should_fail_when_required_fields_are_missing()
    {
        $data = [];

        $validator = Validator::make($data, (new UserStoreRequest())->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('surname', $validator->errors()->toArray());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
        $this->assertArrayHasKey('terms_and_privacy_agreement', $validator->errors()->toArray());
    }

    #[Test]
    public function it_should_fail_with_invalid_email()
    {
        $data = $this->getBaseValidData();
        $data['email'] = 'not-an-email';

        $validator = Validator::make($data, (new UserStoreRequest())->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    #[Test]
    public function it_should_fail_with_invalid_number_format()
    {
        $data = $this->getBaseValidData();
        $data['number'] = '123'; // Muito curto

        $validator = Validator::make($data, (new UserStoreRequest())->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('number', $validator->errors()->toArray());
    }

    #[Test]
    public function it_should_fail_with_weak_password()
    {
        $data = $this->getBaseValidData();
        $data['password'] = $data['password_confirm'] = 'abcdefgh'; // só letras

        $validator = Validator::make($data, (new UserStoreRequest())->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    #[Test]
    public function it_should_fail_when_passwords_do_not_match()
    {
        $data = $this->getBaseValidData();
        $data['password_confirm'] = 'different123';

        $validator = Validator::make($data, (new UserStoreRequest())->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password_confirm', $validator->errors()->toArray());
    }

    #[Test]
    public function it_should_fail_when_terms_are_not_accepted()
    {
        $data = $this->getBaseValidData();
        $data['terms_and_privacy_agreement'] = false;

        $validator = Validator::make($data, (new UserStoreRequest())->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('terms_and_privacy_agreement', $validator->errors()->toArray());
    }

    private function getBaseValidData(): array
    {
        return [
            'name' => 'Murilo',
            'surname' => 'Figueiredo',
            'number' => '1234567890123',
            'email' => 'murilo@example.com',
            'password' => 'password123',
            'password_confirm' => 'password123',
            'terms_and_privacy_agreement' => true,
            'remember' => '1',
        ];
    }
}
