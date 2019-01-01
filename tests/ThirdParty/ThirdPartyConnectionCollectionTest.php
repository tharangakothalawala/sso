<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 01-01-2019
 */

namespace TSK\SSO\ThirdParty;

use Mockery;
use PHPUnit\Framework\TestCase;

class ThirdPartyConnectionCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeAbleToRetrieveTheAddedConnectionByName()
    {
        $connectionMock1 = Mockery::mock('\TSK\SSO\ThirdParty\VendorConnection');
        $connectionMock2 = Mockery::mock('\TSK\SSO\ThirdParty\VendorConnection');

        $sut = new ThirdPartyConnectionCollection();

        $sut->add('VENDOR_1', $connectionMock1);
        $sut->add('VENDOR_2', $connectionMock2);

        $this->assertSame($connectionMock1, $sut->getByVendor('VENDOR_1'));
        $this->assertSame($connectionMock2, $sut->getByVendor('VENDOR_2'));
    }

    /**
     * @test
     * @expectedException \TSK\SSO\ThirdParty\Exception\UnknownVendorRequestException
     * @expectedExceptionMessage Given vendor 'VENDOR_2' is not yet configured
     */
    public function shouldThrowExceptionOnNonExistingConnectionTypeRequest()
    {
        $connectionMock1 = Mockery::mock('\TSK\SSO\ThirdParty\VendorConnection');

        $sut = new ThirdPartyConnectionCollection();
        $sut->add('VENDOR_1', $connectionMock1);

        // NOTE: requesting for a type that we didn't push
        $sut->getByVendor('VENDOR_2');
    }
}
