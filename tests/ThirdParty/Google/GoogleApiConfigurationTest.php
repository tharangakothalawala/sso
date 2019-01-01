<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 01-01-2019
 */

namespace TSK\SSO\ThirdParty\Google;

use PHPUnit\Framework\TestCase;

class GoogleApiConfigurationTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnWithInstantiatedValues()
    {
        $apiConfig = array(
            'appId' => 'app-id',
            'appSecret' => 'secret',
            'appPermissions' => array('permission1', 'permission2'),
            'redirectUrl' => 'http://www.tsk-webdevelopment.com',
        );

        $sut = new GoogleApiConfiguration(
            $apiConfig['appId'],
            $apiConfig['appSecret'],
            $apiConfig['appPermissions'],
            $apiConfig['redirectUrl']
        );

        $this->assertSame($apiConfig['appId'], $sut->appId());
        $this->assertSame($apiConfig['appSecret'], $sut->appSecret());
        $this->assertSame($apiConfig['appPermissions'], $sut->appPermissions());
        $this->assertSame($apiConfig['redirectUrl'], $sut->redirectUrl());
    }
}
