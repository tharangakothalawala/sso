<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 01-01-2019
 */

namespace TSK\SSO\Auth;

use Mockery;
use PHPUnit\Framework\TestCase;
use TSK\SSO\AppUser\ExistingAppUser;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\ThirdPartyUser;

class AppUserAwarePersistingAuthenticatorTest extends TestCase
{
    /**
     * @test
     */
    public function shouldAuthenticateUsingAVendorConnectionGivenAnAppUser()
    {
        $vendorPlatformEmail = 'vendor-email@test.com';

        $testAppUser = new ExistingAppUser('userId', 'app-email@test.com');
        $testThirdPartyUser = new ThirdPartyUser('id', 'name', $vendorPlatformEmail);
        $testAccessToken = new CommonAccessToken('token', 'vendor');

        $expectedAppUser = new ExistingAppUser('userId', $vendorPlatformEmail);

        $vendorConnectionMock = Mockery::mock('\TSK\SSO\ThirdParty\VendorConnection');
        $vendorConnectionMock->shouldReceive('grantNewAccessToken')
            ->once()
            ->andReturn($testAccessToken);
        $vendorConnectionMock->shouldReceive('getSelf')
            ->once()
            ->with($testAccessToken)
            ->andReturn($testThirdPartyUser);

        $thirdPartyStorageRepositoryMock = Mockery::mock('\TSK\SSO\Storage\ThirdPartyStorageRepository');
        $thirdPartyStorageRepositoryMock->shouldReceive('save')->once();

        $sut = new AppUserAwarePersistingAuthenticator($testAppUser, $thirdPartyStorageRepositoryMock);
        $authenticatedUser = $sut->authenticate($vendorConnectionMock);

        $this->assertEquals($expectedAppUser, $authenticatedUser);
        $this->assertSame($expectedAppUser->id(), $authenticatedUser->id());
        $this->assertSame($vendorPlatformEmail, $authenticatedUser->email());
        $this->assertSame(true, $authenticatedUser->isExistingUser());
    }
}
