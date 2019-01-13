<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 13-01-2019
 */

namespace TSK\SSO\ThirdParty\Twitter;

use PHPUnit\Framework\TestCase;

class TwitterApiConfigurationTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnWithInstantiatedValues()
    {
        $apiConfig = array(
            'apiKey' => 'api-key',
            'apiSecret' => 'api-secret',
            'token' => 'the-token',
            'tokenSecret' => 'the-token-secret',
            'redirectUrl' => 'http://www.tsk-webdevelopment.com',
        );

        $sut = new TwitterApiConfiguration(
            $apiConfig['apiKey'],
            $apiConfig['apiSecret'],
            $apiConfig['token'],
            $apiConfig['tokenSecret'],
            $apiConfig['redirectUrl']
        );

        $this->assertSame($apiConfig['apiKey'], $sut->consumerApiKey());
        $this->assertSame($apiConfig['apiSecret'], $sut->consumerApiSecret());
        $this->assertSame($apiConfig['token'], $sut->oauthToken());
        $this->assertSame($apiConfig['tokenSecret'], $sut->oauthTokenSecret());
        $this->assertSame($apiConfig['redirectUrl'], $sut->redirectUrl());
    }
}
