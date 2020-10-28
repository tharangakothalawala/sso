<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   28-10-2020
 */

namespace TSK\SSO\ThirdParty\Zoom;

use PHPUnit\Framework\TestCase;

class ZoomConnectionFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnAnInstanceOfAZoomConnection()
    {
        $sut = new ZoomConnectionFactory();

        $actualConnection = $sut->get('clientId', 'clientSecret', 'www.tsk-webdevelopment.com');

        $this->assertInstanceOf('\TSK\SSO\ThirdParty\Zoom\ZoomConnection', $actualConnection);
    }
}
