<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 01-01-2019
 */

namespace TSK\SSO\ThirdParty\Yahoo;

use PHPUnit\Framework\TestCase;

class YahooConnectionFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnAnInstanceOfAYahooConnection()
    {
        $sut = new YahooConnectionFactory();

        $actualConnection = $sut->get('clientId', 'clientSecret', 'www.tsk-webdevelopment.com');

        $this->assertInstanceOf('\TSK\SSO\ThirdParty\Yahoo\YahooConnection', $actualConnection);
    }
}
