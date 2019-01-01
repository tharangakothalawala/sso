<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 01-01-2019
 */

namespace TSK\SSO\AppUser;

use PHPUnit\Framework\TestCase;

class ExistingAppUserTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnWithInstantiatedValues()
    {
        $user = array(
            'id' => '6ff8db6b-af54-4580-9c84-ce79ad4afe2c',
            'email' => 'the-email@test.com',
        );

        $sut = new ExistingAppUser($user['id'], $user['email']);

        $this->assertSame($user['id'], $sut->id());
        $this->assertSame($user['email'], $sut->email());
        $this->assertSame(true, $sut->isExistingUser());
    }
}
