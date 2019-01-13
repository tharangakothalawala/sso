<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 01-01-2019
 */

namespace TSK\SSO\ThirdParty\Google;

use PHPUnit\Framework\TestCase;

class GoogleConnectionFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnAnInstanceOfAGoogleConnection()
    {
        $sut = new GoogleConnectionFactory();

        $actualConnection = $sut->get('clientId', 'clientSecret', 'www.tsk-webdevelopment.com');

        $this->assertInstanceOf('\TSK\SSO\ThirdParty\Google\GoogleConnection', $actualConnection);
    }
}
