<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 01/01/2019
 */

namespace TSK\SSO\Storage;

use PHPUnit\Framework\TestCase;
use TSK\SSO\AppUser\NewAppUser;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\ThirdPartyUser;

class FileSystemThirdPartyStorageRepositoryTest extends TestCase
{
    public function tearDown()
    {
        @unlink(__DIR__ . '/' . FileSystemThirdPartyStorageRepository::FILE_NAME);
    }

    /**
     * @test
     */
    public function shouldStoreNewUserDataInTheThirdPartyStore()
    {
        $testThirdPartyUser = new ThirdPartyUser('id', 'name', 'vendor-email@test.com');
        $testNewUser = new NewAppUser('id', $testThirdPartyUser->email());
        $testAccessToken = new CommonAccessToken('token', 'vendor');
        $now = date('Y-m-d H:i:00');

        $sut = new FileSystemThirdPartyStorageRepository(__DIR__);
        $sut->save($testNewUser, $testThirdPartyUser, $testAccessToken);

        $actualContents = file_get_contents(__DIR__ . '/' . FileSystemThirdPartyStorageRepository::FILE_NAME);

        $this->assertSame(
            sprintf('{"vendor::vendor-email@test.com":{"app_user_id":"id","vendor_name":"vendor","vendor_email":"vendor-email@test.com","vendor_access_token":"token","vendor_data":"{\"id\":\"id\",\"name\":\"name\",\"email\":\"vendor-email@test.com\",\"avatar\":\"\",\"gender\":\"\"}","created_at":"%s"}}', $now),
            $actualContents
        );
    }

    /**
     * @test
     */
    public function shouldQueryThirdPartyStoreByReadingTheFile()
    {
        file_put_contents(
            __DIR__ . '/' . FileSystemThirdPartyStorageRepository::FILE_NAME,
            '{"vendor::vendor-email@test.com":{"app_user_id":"id","vendor_name":"vendor","vendor_email":"vendor-email@test.com","vendor_access_token":"token","vendor_data":"{\"id\":\"id\",\"name\":\"name\",\"email\":\"vendor-email@test.com\",\"avatar\":\"\",\"gender\":\"\"}","created_at":"2019-01-01 17:23:00"}}'
        );

        $sut = new FileSystemThirdPartyStorageRepository(__DIR__);

        $this->assertNull($sut->getUser('vendor-email@test.com', 'unknown_vendor')); // known vendor name is : vendor
        $this->assertInstanceOf('\TSK\SSO\Storage\MappedUser', $sut->getUser('vendor-email@test.com'));
        $this->assertSame('id', $sut->getUser('vendor-email@test.com')->appUserId());
        $this->assertSame('vendor', $sut->getUser('vendor-email@test.com')->vendorName());
        $this->assertSame('vendor-email@test.com', $sut->getUser('vendor-email@test.com')->vendorEmail());
        $this->assertSame('token', $sut->getUser('vendor-email@test.com')->vendorToken());
        $this->assertSame(
            '{"id":"id","name":"name","email":"vendor-email@test.com","avatar":"","gender":""}',
            $sut->getUser('vendor-email@test.com')->vendorData()
        );
        $this->assertSame(
            array(
                'id' => 'id',
                'name' => 'name',
                'email' => 'vendor-email@test.com',
                'avatar' => '',
                'gender' => '',
            ),
            $sut->getUser('vendor-email@test.com')->decodedVendorData()
        );
    }

    /**
     * @test
     */
    public function shouldRemoveFromTHirdPartyStore()
    {
        file_put_contents(
            __DIR__ . '/' . FileSystemThirdPartyStorageRepository::FILE_NAME,
            '{"vendor::vendor-email@test.com":{"app_user_id":"id","vendor_name":"vendor","vendor_email":"vendor-email@test.com","vendor_access_token":"token","vendor_data":"{\"id\":\"id\",\"name\":\"name\",\"email\":\"vendor-email@test.com\",\"avatar\":\"\",\"gender\":\"\"}","created_at":"2019-01-01 17:23:00"}}'
        );

        $sut = new FileSystemThirdPartyStorageRepository(__DIR__);

        // let's make sure we have it in the store
        $this->assertInstanceOf('\TSK\SSO\Storage\MappedUser', $sut->getUser('vendor-email@test.com'));

        // testing the removal
        $sut->remove('vendor-email@test.com', 'vendor');
        $this->assertNull($sut->getUser('vendor-email@test.com'));
    }
}
