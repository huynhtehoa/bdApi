<?php

namespace tests\api;

use tests\bases\ApiTestCase;

class UserTest extends ApiTestCase
{
    /**
     * @var string
     */
    private static $accessToken = '';

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $token = static::postPassword(static::dataApiClient(), static::dataUser());
        self::$accessToken = $token['access_token'];
    }

    public function testGetIndex()
    {
        $user = $this->dataUser();

        $jsonUsers = static::httpRequestJson('GET', 'users?oauth_token=' . self::$accessToken);
        $this->assertArrayHasKey('users', $jsonUsers);

        // test exclude fields.
        $excludeFields = [
            '',
            'user_last_seen_date',
            'user_external_authentications',
            'user_dob_day',
            'user_dob_month',
            'user_dob_year',
            'user_has_password'
        ];

        foreach ($excludeFields as $excludeField) {
            $jsonUser = static::httpRequestJson(
                'GET',
                "users/{$user['user_id']}?exclude_field={$excludeField}&oauth_token=" . self::$accessToken
            );

            $this->assertArrayHasKey('user', $jsonUser);

            if ($excludeField) {
                $this->assertArrayNotHasKey($excludeField, $jsonUser);
            }
        }
    }

    public function testPostIndex()
    {
        $now = time();

        $userEmail = 'tests_' . $now . '@local.com';
        $username = 'tests_' . $now;

        $json = $this->httpRequestJson(
            'POST',
            'users',
            [
                'form_params' => [
                    'user_email' => $userEmail,
                    'username' => $username,
                    'password' => '123456',
                    'oauth_token' => self::$accessToken
                ]
            ]
        );

        $this->assertArrayHasKey('user', $json);
        $this->assertArrayHasKey('token', $json);

        $token = $json['token'];
        $this->assertArrayHasKey('access_token', $token);
        $this->assertArrayHasKey('expires_in', $token);
        $this->assertArrayHasKey('scope', $token);
        $this->assertArrayHasKey('refresh_token', $token);
    }

    public function testPutIndex()
    {
        $user = $this->dataUser();

        $json = $this->httpRequestJson(
            'PUT',
            'users/' . $user['user_id'],
            [
                'form_params' => [
                    'oauth_token' => self::$accessToken
                ]
            ]
        );

        $this->assertArrayHasKey('status', $json);
        $this->assertEquals('ok', $json['status']);
    }
}
