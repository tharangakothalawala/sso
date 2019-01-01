<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 01-01-2019
 */

namespace TSK\SSO\ThirdParty\Facebook;

use Mockery;
use PHPUnit\Framework\TestCase;

class FacebookConnectionFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnAnInstanceOfAFacebookConnection()
    {
        $thirdPartyStorageRepositoryMock = Mockery::mock('\TSK\SSO\Storage\ThirdPartyStorageRepository');

        $sut = new FacebookConnectionFactory($thirdPartyStorageRepositoryMock, 'v100', 'permissions');

        $actualConnection = $sut->get('clientId', 'clientSecret', 'www.tsk-webdevelopment.com');

        $this->assertInstanceOf('\TSK\SSO\ThirdParty\Facebook\FacebookConnection', $actualConnection);
    }
}
