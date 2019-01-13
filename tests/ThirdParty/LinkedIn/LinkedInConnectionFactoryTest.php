<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 01-01-2019
 */

namespace TSK\SSO\ThirdParty\LinkedIn;

use PHPUnit\Framework\TestCase;

class LinkedInConnectionFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnAnInstanceOfALinkedInConnection()
    {
        $sut = new LinkedInConnectionFactory('permissions');

        $actualConnection = $sut->get('clientId', 'clientSecret', 'www.tsk-webdevelopment.com');

        $this->assertInstanceOf('\TSK\SSO\ThirdParty\LinkedIn\LinkedInConnection', $actualConnection);
    }
}
