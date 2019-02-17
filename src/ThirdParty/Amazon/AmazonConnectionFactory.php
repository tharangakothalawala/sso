<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 17-02-2019
 */

namespace TSK\SSO\ThirdParty\Amazon;

use TSK\SSO\Http\CurlRequest;
use TSK\SSO\ThirdParty\VendorConnection;
use TSK\SSO\ThirdParty\VendorConnectionFactory;

/**
 * @package TSK\SSO\ThirdParty\Amazon
 */
class AmazonConnectionFactory implements VendorConnectionFactory
{
    /**
     * Returns a Amazon Connection instance using given credentials. Visit the following link for credentials.
     * @see https://sellercentral.amazon.com/hz/home
     *
     * @param string $clientId the client id which can be generated at the third party portal
     * @param string $clientSecret this can be found similar to the clientId
     * @param string $callbackUrl the url to callback after a third party auth attempt
     * @return VendorConnection
     */
    public function get($clientId, $clientSecret, $callbackUrl)
    {
        return new AmazonConnection(
            new AmazonApiConfiguration(
                $clientId,
                $clientSecret,
                $callbackUrl
            ),
            new CurlRequest()
        );
    }
}
