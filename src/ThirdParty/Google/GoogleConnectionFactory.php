<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 */

namespace TSK\SSO\ThirdParty\Google;

use Google_Service_Plus;
use TSK\SSO\ThirdParty\VendorConnection;
use TSK\SSO\ThirdParty\VendorConnectionFactory;
use TSK\SSO\Http\CurlRequest;

/**
 * @package TSK\SSO\ThirdParty\Google
 */
class GoogleConnectionFactory implements VendorConnectionFactory
{
    /**
     * @param string $clientId the client id which can be generated at the third party portal
     * @param string $clientSecret this can be found similar to the clientId
     * @param string $callbackUrl the url to callback after a third party auth attempt
     * @return VendorConnection
     */
    public function get($clientId, $clientSecret, $callbackUrl)
    {
        return new GoogleConnection(
            new GoogleApiConfiguration(
                $clientId,
                $clientSecret,
                array(
                    Google_Service_Plus::PLUS_ME,
                    Google_Service_Plus::USERINFO_EMAIL,
                    Google_Service_Plus::USERINFO_PROFILE,
                ),
                $callbackUrl
            ),
            new CurlRequest()
        );
    }
}
