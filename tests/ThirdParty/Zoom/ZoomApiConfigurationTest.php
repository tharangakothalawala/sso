<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   28-10-2020
 */

namespace TSK\SSO\ThirdParty\Zoom;

use PHPUnit\Framework\TestCase;

class ZoomApiConfigurationTest extends TestCase
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

        $sut = new ZoomApiConfiguration(
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
