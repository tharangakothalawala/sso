<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 01-01-2019
 */

namespace TSK\SSO\ThirdParty;

use PHPUnit\Framework\TestCase;

class ThirdPartyUserTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnWithInstantiatedValues()
    {
        $vendorUser = array(
            'id' => 'vendor-id',
            'name' => 'test-vendor',
            'email' => 'vendor-email@test.com',
            'pictureUrl' => 'http://pictures/picture.jpg',
            'gender' => 'the-gender',
        );

        $sut = new ThirdPartyUser(
            $vendorUser['id'],
            $vendorUser['name'],
            $vendorUser['email'],
            $vendorUser['pictureUrl'],
            $vendorUser['gender']
        );

        $this->assertSame($vendorUser['id'], $sut->id());
        $this->assertSame($vendorUser['name'], $sut->name());
        $this->assertSame($vendorUser['email'], $sut->email());
        $this->assertSame($vendorUser['pictureUrl'], $sut->avatar());
        $this->assertSame($vendorUser['gender'], $sut->gender());
    }
}
