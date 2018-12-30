<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 */

namespace TSK\SSO\ThirdParty;

/**
 * @package TSK\SSO\ThirdParty
 *
 * Client ID, Client Secret & Callback URL are the mainly used common parameters which are required in an oauth framework.
 * In order to create a new third party vendor connection, you must implement this interface.
 */
interface VendorConnectionFactory
{
    /**
     * @param string $clientId the client id which can be generated at the third party portal
     * @param string $clientSecret this can be found similar to the clientId
     * @param string $callbackUrl the url to callback after a third party auth attempt
     * @return VendorConnection
     */
    public function get($clientId, $clientSecret, $callbackUrl);
}
