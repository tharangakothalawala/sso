<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 17-02-2019
 */

namespace TSK\SSO\ThirdParty;

use TSK\SSO\Storage\MappedUser;
use Mockery;
use PHPUnit\Framework\TestCase;

class VendorConnectionRevokerTest extends TestCase
{
    /**
     * @test
     */
    public function shouldNotRevokeIfNoMappedUserFoundForTheGivenVendorName()
    {
        $storageRepoMock = Mockery::mock('\TSK\SSO\Storage\ThirdPartyStorageRepository');
        $storageRepoMock->shouldReceive('getUser')->andReturn(null);
        $storageRepoMock->shouldReceive('remove')->never();

        $connectionMock = Mockery::mock('\TSK\SSO\ThirdParty\VendorConnection');
        $connectionMock->shouldReceive('revokeAccess')->never();

        $sut = new VendorConnectionRevoker($connectionMock, $storageRepoMock);
        $isRevoked = $sut->revoke('email', 'vendor_name');

        $this->assertFalse($isRevoked);
    }
    /**
     * @test
     */
    public function shouldNotRevokeIfVendorRevokeRequestFails()
    {
        $testEmail = 'vendor_email';
        $testVendor = 'vendor_name';
        $testMappedUser = new MappedUser(
            'app_user_id',
            $testVendor,
            $testEmail,
            'vendor_access_token',
            'vendor_data'
        );

        $storageRepoMock = Mockery::mock('\TSK\SSO\Storage\ThirdPartyStorageRepository');
        $storageRepoMock->shouldReceive('getUser')->andReturn($testMappedUser);
        $storageRepoMock->shouldReceive('remove')->never();

        $connectionMock = Mockery::mock('\TSK\SSO\ThirdParty\VendorConnection');
        $connectionMock->shouldReceive('revokeAccess')->andReturn(false);

        $sut = new VendorConnectionRevoker($connectionMock, $storageRepoMock);
        $isRevoked = $sut->revoke($testEmail, $testVendor);

        $this->assertFalse($isRevoked);
    }

    /**
     * @test
     */
    public function shouldRevokeIfMappedUserFoundForTheGivenVendorName()
    {
        $testEmail = 'vendor_email';
        $testVendor = 'vendor_name';
        $testMappedUser = new MappedUser(
            'app_user_id',
            $testVendor,
            $testEmail,
            'vendor_access_token',
            'vendor_data'
        );

        $storageRepoMock = Mockery::mock('\TSK\SSO\Storage\ThirdPartyStorageRepository');
        $storageRepoMock->shouldReceive('getUser')->andReturn($testMappedUser);
        $storageRepoMock
            ->shouldReceive('remove')
            ->once()
            ->with($testEmail, $testVendor)
            ->andReturn(true);

        $connectionMock = Mockery::mock('\TSK\SSO\ThirdParty\VendorConnection');
        $connectionMock
            ->shouldReceive('revokeAccess')
            ->once()
            ->with(Mockery::on(function ($commonAccessToken) use ($testMappedUser) {
                return ($commonAccessToken->token() === $testMappedUser->vendorToken())
                    && ($commonAccessToken->vendor() === $testMappedUser->vendorName())
                    && ($commonAccessToken->email() === $testMappedUser->vendorEmail());
            }))
            ->andReturn(true);

        $sut = new VendorConnectionRevoker($connectionMock, $storageRepoMock);
        $isRevoked = $sut->revoke($testEmail, $testVendor);

        $this->assertTrue($isRevoked);
    }
}
