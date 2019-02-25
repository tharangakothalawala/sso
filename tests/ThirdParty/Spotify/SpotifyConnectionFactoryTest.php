<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 25-02-2019
 */

namespace TSK\SSO\ThirdParty\Spotify;

use PHPUnit\Framework\TestCase;

class SpotifyConnectionFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnAnInstanceOfASpotifyConnection()
    {
        $sut = new SpotifyConnectionFactory();

        $actualConnection = $sut->get('clientId', 'clientSecret', 'www.tsk-webdevelopment.com');

        $this->assertInstanceOf('\TSK\SSO\ThirdParty\Spotify\SpotifyConnection', $actualConnection);
    }
}
