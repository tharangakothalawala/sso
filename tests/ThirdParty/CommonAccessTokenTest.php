<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 01-01-2019
 */

namespace TSK\SSO\ThirdParty;

use PHPUnit\Framework\TestCase;

class CommonAccessTokenTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnWithInstantiatedValues()
    {
        $token = array(
            'token' => 'the-token',
            'refresh_token' => 'the-refresh-token',
            'vendor' => 'test-vendor',
            'email' => 'vendor-email@test.com',
        );

        $sut = new CommonAccessToken(
            $token['token'],
            $token['vendor'],
            $token['email']
        );
        $sut->setRefreshToken($token['refresh_token']);

        $this->assertSame($token['token'], $sut->token());
        $this->assertSame($token['vendor'], $sut->vendor());
        $this->assertSame($token['email'], $sut->email());
        $this->assertSame($token['refresh_token'], $sut->getRefreshToken());
    }
}
