<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 01-01-2019
 */

namespace TSK\SSO\ThirdParty\Facebook;

use PHPUnit\Framework\TestCase;

class FacebookApiConfigurationTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnWithInstantiatedValues()
    {
        $apiConfig = array(
            'apiVersion' => 'v1.0',
            'appId' => 'app-id',
            'appSecret' => 'secret',
            'appPermissions' => 'permissions',
            'redirectUrl' => 'http://www.tsk-webdevelopment.com',
        );

        $sut = new FacebookApiConfiguration(
            $apiConfig['apiVersion'],
            $apiConfig['appId'],
            $apiConfig['appSecret'],
            $apiConfig['appPermissions'],
            $apiConfig['redirectUrl']
        );

        $this->assertSame($apiConfig['apiVersion'], $sut->apiVersion());
        $this->assertSame($apiConfig['appId'], $sut->appId());
        $this->assertSame($apiConfig['appSecret'], $sut->appSecret());
        $this->assertSame($apiConfig['appPermissions'], $sut->appPermissions());
        $this->assertSame($apiConfig['redirectUrl'], $sut->redirectUrl());
    }
}
