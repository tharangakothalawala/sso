<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 */

namespace TSK\SSO\ThirdParty;

use TSK\SSO\ThirdParty\Exception\UnknownVendorRequestException;

/**
 * @package TSK\SSO\ThirdParty
 *
 * Use this to hold multiple vendor connections if you require.
 */
class ThirdPartyConnectionCollection
{
    /**
     * VendorConnection[] list of configured vendor connections
     */
    private $vendorConnections;

    /**
     * Adds a configured vendor connection into the known list of connections.
     *
     * @param string $vendorName the third party vendor name. ex: google, facebook, linkedin etc
     * @param VendorConnection $vendorConnection
     */
    public function add($vendorName, VendorConnection $vendorConnection)
    {
        $this->vendorConnections[$vendorName] = $vendorConnection;
    }

    /**
     * Returns a requested vendor connection if available or throws an exception.
     *
     * @param string $vendorName the third party vendor name. ex: google, facebook, linkedin etc
     * @return VendorConnection
     * @throws UnknownVendorRequestException
     */
    public function getByVendor($vendorName)
    {
        if (!isset($this->vendorConnections[$vendorName])) {
            throw new UnknownVendorRequestException(sprintf('Given vendor \'%s\' is not yet configured', $vendorName));
        }

        return $this->vendorConnections[$vendorName];
    }
}
