<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 01-01-2019
 */

namespace TSK\SSO\ThirdParty\Slack;

use PHPUnit\Framework\TestCase;

class SlackApiConfigurationTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnWithInstantiatedValues()
    {
        $apiConfig = array(
            'appId' => 'app-id',
            'appSecret' => 'secret',
            'appPermissions' => 'permissions',
            'redirectUrl' => 'http://www.tsk-webdevelopment.com',
        );

        $sut = new SlackApiConfiguration(
            $apiConfig['appId'],
            $apiConfig['appSecret'],
            $apiConfig['appPermissions'],
            $apiConfig['redirectUrl']
        );

        $this->assertSame($apiConfig['appId'], $sut->appId());
        $this->assertSame($apiConfig['appSecret'], $sut->appSecret());
        $this->assertSame($apiConfig['appPermissions'], $sut->appPermissions());
        $this->assertSame($apiConfig['redirectUrl'], $sut->redirectUrl());
        $this->assertSame('dfeb6ef625880832f61c6f4bd737e11b', $sut->ourSecretState());
    }
}
