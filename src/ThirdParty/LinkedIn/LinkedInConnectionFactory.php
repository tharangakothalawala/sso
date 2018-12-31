<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 31-12-2018
 */

namespace TSK\SSO\ThirdParty\LinkedIn;

use TSK\SSO\ThirdParty\VendorConnectionFactory;

/**
 * @package TSK\SSO\ThirdParty\LinkedIn
 */
class LinkedInConnectionFactory implements VendorConnectionFactory
{
    /**
     * @param string $clientId the client id which can be generated at the third party portal
     * @param string $clientSecret this can be found similar to the clientId
     * @param string $callbackUrl the url to callback after a third party auth attempt
     * @return VendorConnection
     */
    public function get($clientId, $clientSecret, $callbackUrl)
    {
        return new LinkedInConnection(
            new LinkedInApiConfiguration($clientId, $clientSecret, $callbackUrl)
        );
    }
}
