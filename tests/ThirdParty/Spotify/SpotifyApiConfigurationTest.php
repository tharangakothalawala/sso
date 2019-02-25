<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 25-02-2019
 */

namespace TSK\SSO\ThirdParty\Spotify;

use PHPUnit\Framework\TestCase;

class SpotifyApiConfigurationTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnWithInstantiatedValues()
    {
        $apiConfig = array(
            'clientId' => 'client-id',
            'clientSecret' => 'client-secret',
            'redirectUrl' => 'http://www.tsk-webdevelopment.com',
        );

        $sut = new SpotifyApiConfiguration(
            $apiConfig['clientId'],
            $apiConfig['clientSecret'],
            $apiConfig['redirectUrl']
        );

        $this->assertSame($apiConfig['clientId'], $sut->clientId());
        $this->assertSame($apiConfig['clientSecret'], $sut->clientSecret());
        $this->assertSame($apiConfig['redirectUrl'], $sut->redirectUrl());
        $this->assertSame('dfeb6ef625880832f61c6f4bd737e11b', $sut->ourSecretState());
    }
}
