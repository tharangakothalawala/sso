<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 01-01-2019
 */

namespace TSK\SSO\Storage;

use PHPUnit\Framework\TestCase;

class MappedUserTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnWithInstantiatedValues()
    {
        $mappedUser = array(
            'app_user_id' => '6ff8db6b-af54-4580-9c84-ce79ad4afe2c',
            'vendor_name' => 'test-vendor',
            'vendor_email' => 'vendor-email@test.com',
            'vendor_token' => 'the-token',
            'vendor_data' => '{"foo" : "bar"}',
        );

        $sut = new MappedUser(
            $mappedUser['app_user_id'],
            $mappedUser['vendor_name'],
            $mappedUser['vendor_email'],
            $mappedUser['vendor_token'],
            $mappedUser['vendor_data']
        );

        $this->assertSame($mappedUser['app_user_id'], $sut->appUserId());
        $this->assertSame($mappedUser['vendor_name'], $sut->vendorName());
        $this->assertSame($mappedUser['vendor_email'], $sut->vendorEmail());
        $this->assertSame($mappedUser['vendor_token'], $sut->vendorToken());
        $this->assertSame($mappedUser['vendor_data'], $sut->vendorData());
        $this->assertSame(array('foo' => 'bar'), $sut->decodedVendorData());
    }
}
