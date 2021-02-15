<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   15-02-2021
 */

namespace TSK\SSO\ThirdParty\Stripe;

use PHPUnit\Framework\TestCase;

class StripeConnectionFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnAnInstanceOfAStripeConnection()
    {
        $sut = new StripeConnectionFactory();

        $actualConnection = $sut->get('clientId', 'clientSecret', 'www.tsk-webdevelopment.com');

        $this->assertInstanceOf('\TSK\SSO\ThirdParty\Stripe\StripeConnection', $actualConnection);
    }
}
