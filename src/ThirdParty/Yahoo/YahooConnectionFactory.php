<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 22-01-2019
 */

namespace TSK\SSO\ThirdParty\Yahoo;

use TSK\SSO\Http\CurlRequest;
use TSK\SSO\ThirdParty\VendorConnection;
use TSK\SSO\ThirdParty\VendorConnectionFactory;

/**
 * @package TSK\SSO\ThirdParty\Yahoo
 */
class YahooConnectionFactory implements VendorConnectionFactory
{
    /**
     * Returns a Yahoo Connection instance using given credentials. Visit the following link for credentials.
     * You must select 'Read/Write Public and Private' of 'Profiles (Social Directory)' API Permissions for this to work.
     * @see https://developer.yahoo.com/apps
     *
     * @param string $clientId the client id which can be generated at the third party portal
     * @param string $clientSecret this can be found similar to the clientId
     * @param string $callbackUrl the url to callback after a third party auth attempt
     * @return VendorConnection
     */
    public function get($clientId, $clientSecret, $callbackUrl)
    {
        return new YahooConnection(
            new YahooApiConfiguration(
                $clientId,
                $clientSecret,
                $callbackUrl
            ),
            new CurlRequest()
        );
    }
}
