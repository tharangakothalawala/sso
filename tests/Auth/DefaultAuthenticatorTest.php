<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 01-01-2019
 */

namespace TSK\SSO\Auth;

use Mockery;
use PHPUnit\Framework\TestCase;
use TSK\SSO\AppUser\ExistingAppUser;
use TSK\SSO\AppUser\NewAppUser;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\ThirdPartyUser;

class DefaultAuthenticatorTest extends TestCase
{
    /**
     * @var string
     */
    private $testAppEmail = 'app-email@test.com';

    /**
     * @var string
     */
    private $testVendorEmail = 'vendor-email@test.com';

    /**
     * @var CommonAccessToken
     */
    private $testAccessToken;

    public function setUp()
    {
        $this->testAccessToken = new CommonAccessToken('token', 'vendor');
    }

    /**
     * @test
     */
    public function shouldAuthenticateUsingWhenExistingUserFound()
    {
        $expectedAppUser = $testExistingAppUser = new ExistingAppUser('userId', $this->testAppEmail);
        $testThirdPartyUser = new ThirdPartyUser('id', 'name', $this->testVendorEmail);

        $vendorConnectionMock = $this->getVendorConnectionMock($testThirdPartyUser);

        $appUserRepositoryMock = Mockery::mock('\TSK\SSO\AppUser\AppUserRepository');
        $appUserRepositoryMock->shouldReceive('getUser')
            ->once()
            ->with($this->testVendorEmail)
            // NOTE : an Existing User Found
            ->andReturn($testExistingAppUser);

        $sut = new DefaultAuthenticator($appUserRepositoryMock);
        $authenticatedUser = $sut->authenticate($vendorConnectionMock);

        $this->assertEquals($expectedAppUser, $authenticatedUser);
        $this->assertSame($expectedAppUser->id(), $authenticatedUser->id());
        $this->assertSame($expectedAppUser->email(), $authenticatedUser->email());
        $this->assertSame(true, $authenticatedUser->isExistingUser());
    }

    /**
     * @test
     */
    public function shouldAuthenticateAndProvisionNewUserWhenNoUserFoundAtAll()
    {
        $testThirdPartyUser = new ThirdPartyUser('id', 'name', $this->testVendorEmail);
        $expectedAppUser = $testNewAppUser = new NewAppUser('new-userId', $testThirdPartyUser->email());

        $vendorConnectionMock = $this->getVendorConnectionMock($testThirdPartyUser);

        $appUserRepositoryMock = Mockery::mock('\TSK\SSO\AppUser\AppUserRepository');
        $appUserRepositoryMock->shouldReceive('getUser')
            ->once()
            ->with($testThirdPartyUser->email())
            // NOTE : no user found in the main user store.
            ->andReturnNull();
        $appUserRepositoryMock->shouldReceive('create')
            ->once()
            ->with($testThirdPartyUser)
            // NOTE: returns a newly created user
            ->andReturn($testNewAppUser);

        $sut = new DefaultAuthenticator($appUserRepositoryMock);
        $authenticatedUser = $sut->authenticate($vendorConnectionMock);

        $this->assertEquals($expectedAppUser, $authenticatedUser);
        $this->assertSame($expectedAppUser->id(), $authenticatedUser->id());
        $this->assertSame($expectedAppUser->email(), $authenticatedUser->email());
        $this->assertSame(false, $authenticatedUser->isExistingUser());
    }

    /**
     * @param ThirdPartyUser $testThirdPartyUser
     * @return Mockery\MockInterface|\TSK\SSO\ThirdParty\VendorConnection
     */
    private function getVendorConnectionMock(ThirdPartyUser $testThirdPartyUser)
    {
        $vendorConnectionMock = Mockery::mock('\TSK\SSO\ThirdParty\VendorConnection');
        $vendorConnectionMock->shouldReceive('grantNewAccessToken')
            ->once()
            ->andReturn($this->testAccessToken);
        $vendorConnectionMock->shouldReceive('getSelf')
            ->once()
            ->with($this->testAccessToken)
            ->andReturn($testThirdPartyUser);

        return $vendorConnectionMock;
    }
}
