<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   28-10-2020
 */

namespace TSK\SSO\ThirdParty\Zoom;

use TSK\SSO\ThirdParty\VendorConnection;
use TSK\SSO\ThirdParty\VendorConnectionFactory;
use TSK\SSO\Http\CurlRequest;

/**
 * @package TSK\SSO\ThirdParty\Zoom
 */
class ZoomConnectionFactory implements VendorConnectionFactory
{
    /**
     * @param string $clientId     the client id which can be generated at the third party portal
     * @param string $clientSecret this can be found similar to the clientId
     * @param string $callbackUrl  the url to callback after a third party auth attempt
     *
     * @return VendorConnection
     */
    public function get($clientId, $clientSecret, $callbackUrl)
    {
        return new ZoomConnection(
            new ZoomApiConfiguration(
                $clientId,
                $clientSecret,
                $callbackUrl
            ),
            new CurlRequest()
        );
    }
}
