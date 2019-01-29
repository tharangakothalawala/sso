<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 29-01-2019
 */

namespace TSK\SSO\ThirdParty\GitHub;

use PHPUnit\Framework\TestCase;

class GitHubApiConfigurationTest extends TestCase
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
            'oauthAppName' => 'TestAppName',
        );

        $sut = new GitHubApiConfiguration(
            $apiConfig['clientId'],
            $apiConfig['clientSecret'],
            $apiConfig['redirectUrl'],
            $apiConfig['oauthAppName']
        );

        $this->assertSame($apiConfig['clientId'], $sut->clientId());
        $this->assertSame($apiConfig['clientSecret'], $sut->clientSecret());
        $this->assertSame($apiConfig['redirectUrl'], $sut->redirectUrl());
        $this->assertSame($apiConfig['oauthAppName'], $sut->oauthAppName());
        $this->assertSame('dfeb6ef625880832f61c6f4bd737e11b', $sut->ourSecretState());
    }
}
