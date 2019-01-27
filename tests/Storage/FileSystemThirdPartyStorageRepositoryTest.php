<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 01/01/2019
 */

namespace TSK\SSO\Storage;

use PHPUnit\Framework\TestCase;
use TSK\SSO\AppUser\ExistingAppUser;
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
            '{'
            . '"vendor3::vendor3-email@test.com":{"app_user_id":100,"vendor_name":"vendor3","vendor_email":"vendor3-email@test.com","vendor_access_token":"token3","vendor_data":"{\"id\":\"id\",\"name\":\"name\",\"email\":\"vendor3-email@test.com\",\"avatar\":\"\",\"gender\":\"unknown\"}","created_at":"2019-01-26 12:58:00"},'
            . '"vendor1::vendor1-email@test.com":{"app_user_id":934,"vendor_name":"vendor1","vendor_email":"vendor1-email@test.com","vendor_access_token":"token1","vendor_data":"{\"id\":\"id\",\"name\":\"name\",\"email\":\"vendor1-email@test.com\",\"avatar\":\"\",\"gender\":\"male\"}","created_at":"2019-01-26 12:58:00"},'
            . '"vendor2::vendor2-email@test.com":{"app_user_id":934,"vendor_name":"vendor2","vendor_email":"vendor2-email@test.com","vendor_access_token":"token2","vendor_data":"{\"id\":\"id\",\"name\":\"name\",\"email\":\"vendor2-email@test.com\",\"avatar\":\"\",\"gender\":\"female\"}","created_at":"2019-01-26 12:58:00"}'
            . '}'
        );

        $sut = new FileSystemThirdPartyStorageRepository(__DIR__);

        $mappedUser = $sut->getUser('vendor2-email@test.com');

        $this->assertNull($sut->getUser('vendor2-email@test.com', 'unknown_vendor')); // known vendor name is : vendor
        $this->assertInstanceOf('\TSK\SSO\Storage\MappedUser', $mappedUser);
        $this->assertSame(934, $mappedUser->appUserId());
        $this->assertSame('vendor2', $mappedUser->vendorName());
        $this->assertSame('vendor2-email@test.com', $mappedUser->vendorEmail());
        $this->assertSame('token2', $mappedUser->vendorToken());
        $this->assertSame(
            '{"id":"id","name":"name","email":"vendor2-email@test.com","avatar":"","gender":"female"}',
            $mappedUser->vendorData()
        );
        $this->assertSame(
            array(
                'id' => 'id',
                'name' => 'name',
                'email' => 'vendor2-email@test.com',
                'avatar' => '',
                'gender' => 'female',
            ),
            $mappedUser->decodedVendorData()
        );
    }

    /**
     * @test
     */
    public function shouldReturnTheMappedVendorConnectionsForAGivenAppUser()
    {
        $testUserId = 934;
        file_put_contents(
            __DIR__ . '/' . FileSystemThirdPartyStorageRepository::FILE_NAME,
            '{'
            . '"vendor3::vendor3-email@test.com":{"app_user_id":100,"vendor_name":"vendor3","vendor_email":"vendor3-email@test.com","vendor_access_token":"token3","vendor_data":"{\"id\":\"id\",\"name\":\"name\",\"email\":\"vendor3-email@test.com\",\"avatar\":\"\",\"gender\":\"unknown\"}","created_at":"2019-01-26 12:58:00"},'
            . '"vendor1::vendor1-email@test.com":{"app_user_id":934,"vendor_name":"vendor1","vendor_email":"vendor1-email@test.com","vendor_access_token":"token1","vendor_data":"{\"id\":\"id\",\"name\":\"name\",\"email\":\"vendor1-email@test.com\",\"avatar\":\"\",\"gender\":\"male\"}","created_at":"2019-01-26 12:58:00"},'
            . '"vendor2::vendor2-email@test.com":{"app_user_id":934,"vendor_name":"vendor2","vendor_email":"vendor2-email@test.com","vendor_access_token":"token2","vendor_data":"{\"id\":\"id\",\"name\":\"name\",\"email\":\"vendor2-email@test.com\",\"avatar\":\"\",\"gender\":\"female\"}","created_at":"2019-01-26 12:58:00"}'
            . '}'
        );

        $sut = new FileSystemThirdPartyStorageRepository(__DIR__);

        $expectedMappedUsers = array(
            new MappedUser($testUserId, 'vendor1', 'vendor1-email@test.com', 'token1', '{"id":"id","name":"name","email":"vendor1-email@test.com","avatar":"","gender":"male"}'),
            new MappedUser($testUserId, 'vendor2', 'vendor2-email@test.com', 'token2', '{"id":"id","name":"name","email":"vendor2-email@test.com","avatar":"","gender":"female"}'),
        );

        $this->assertEquals(
            $expectedMappedUsers,
            $sut->getByAppUser(new ExistingAppUser($testUserId, 'vendor1-email@test.com'))
        );
    }

    /**
     * @test
     */
    public function shouldRemoveFromThirdPartyStore()
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
