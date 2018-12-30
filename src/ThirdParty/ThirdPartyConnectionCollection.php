<?php
/**
 * @author : Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   : 30-12-2018
 */

namespace TSK\SSO\ThirdParty;

/**
 * @package TSK\SSO\ThirdParty
 *
 * Use this to hold multiple vendor connections if you require.
 */
class ThirdPartyConnectionCollection
{
    /**
     * VendorConnection[]
     */
    private $vendorConnections;

    /**
     * @param string $vendorName the third party vendor name. ex: google, facebook, linkedin etc
     * @param VendorConnection $vendorConnection
     */
    public function add($vendorName, VendorConnection $vendorConnection)
    {
        $this->vendorConnections[$vendorName] = $vendorConnection;
    }

    /**
     * @param string $vendorName the third party vendor name. ex: google, facebook, linkedin etc
     * @return VendorConnection|null
     */
    public function getByVendor($vendorName)
    {
        if (!isset($this->vendorConnections[$vendorName])) {
            return null;
        }

        return $this->vendorConnections[$vendorName];
    }
}
