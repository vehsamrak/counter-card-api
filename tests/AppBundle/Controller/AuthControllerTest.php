<?php

namespace AppBundle\Controller;

use AppBundle\Fixture\UserFixture;
use Tests\RestTestCase;

/**
 * @author Vehsamrak
 */
class AuthControllerTest extends RestTestCase
{

    private const INVALID_LOGIN = 'invalid-login';
    private const INVALID_PASSWORD = 'invalid-password';
    private const VALID_LOGIN = 'test@test.ru';
    private const VALID_PASSWORD = 'password';
    private const AUTH_TOKEN = 'test-token';

    protected function setUp()
    {
        $this->httpClient = $this->makeClient();
        $this->loadFixtures(
            [
                UserFixture::class,
            ]
        );
    }

    /** @test */
    public function POST_loginWithoutParameters_400(): void
    {
        $this->sendPostRequest('/api/login');

        $this->assertHttpCode(400);
    }

    /** @test */
    public function POST_loginWithInvalidLoginAndPassword_403(): void
    {
        $parameters = [
            'login'    => self::INVALID_LOGIN,
            'password' => self::INVALID_PASSWORD,
        ];

        $this->sendPostRequest('/api/login', $parameters);

        $this->assertHttpCode(403);
    }

    /** @test */
    public function POST_loginWithValidLoginAndPassword_200AndTokenReturned(): void
    {
        $parameters = [
            'login'    => self::VALID_LOGIN,
            'password' => self::VALID_PASSWORD,
        ];

        $this->sendPostRequest('/api/login', $parameters);

        $this->assertHttpCode(200);
        // exactly 8 any characters
        $this->assertRegExp('/^.{8}$/', $this->getResponseContents());
        $this->assertNotEquals(self::AUTH_TOKEN, $this->getResponseContents());
    }
}
