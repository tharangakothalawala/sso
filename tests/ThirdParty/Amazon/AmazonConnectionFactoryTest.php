<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 17-02-2019
 */

namespace TSK\SSO\ThirdParty\Amazon;

use PHPUnit\Framework\TestCase;

class AmazonConnectionFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnAnInstanceOfAnAmazonConnection()
    {
        $sut = new AmazonConnectionFactory('TestAppName');

        $actualConnection = $sut->get('clientId', 'clientSecret', 'www.tsk-webdevelopment.com');

        $this->assertInstanceOf('\TSK\SSO\ThirdParty\Amazon\AmazonConnection', $actualConnection);
    }
}
