<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   15-02-2021
 */

namespace TSK\SSO\ThirdParty\Stripe;

use PHPUnit\Framework\TestCase;

class StripeConfigurationTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnWithInstantiatedValues()
    {
        $apiConfig = array(
            'apiKey' => 'api-key',
            'apiSecret' => 'api-secret',
            'redirectUrl' => 'http://www.tsk-webdevelopment.com',
        );

        $sut = new StripeConfiguration(
            $apiConfig['apiKey'],
            $apiConfig['apiSecret'],
            $apiConfig['redirectUrl']
        );

        $this->assertSame($apiConfig['apiKey'], $sut->clientId());
        $this->assertSame($apiConfig['apiSecret'], $sut->clientSecret());
        $this->assertSame($apiConfig['redirectUrl'], $sut->redirectUrl());
    }
}
