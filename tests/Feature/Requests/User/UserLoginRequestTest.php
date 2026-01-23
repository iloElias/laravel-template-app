<?php

namespace Tests\Feature\Requests\User;

use App\Http\Requests\User\UserLoginRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @internal
 */
class UserLoginRequestTest extends TestCase
{
    #[Test]
    public function it_should_fail_when_required_fields_are_missing()
    {
        $data = [];

        $validator = Validator::make($data, (new UserLoginRequest())->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    #[Test]
    public function it_should_fail_with_invalid_email_format()
    {
        $data = $this->getValidData();
        $data['email'] = 'not-an-email';

        $validator = Validator::make($data, (new UserLoginRequest())->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    #[Test]
    public function it_should_fail_with_weak_password()
    {
        $data = $this->getValidData();
        $data['password'] = 'abcdefghi';

        $validator = Validator::make($data, (new UserLoginRequest())->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    #[Test]
    public function it_should_fail_with_short_password()
    {
        $data = $this->getValidData();
        $data['password'] = 'abc12';

        $validator = Validator::make($data, (new UserLoginRequest())->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    #[Test]
    public function it_should_pass_with_valid_data()
    {
        $data = $this->getValidData();

        $rules = (new UserLoginRequest())->rules();
        $rules['email'] = 'required|email|max:255';

        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->passes());
    }

    private function getValidData(): array
    {
        return [
            'email' => 'murilo@example.com',
            'password' => 'Password123',
        ];
    }
}
