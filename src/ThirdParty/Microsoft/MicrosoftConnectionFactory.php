<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 02-02-2019
 */

namespace TSK\SSO\ThirdParty\Microsoft;

use TSK\SSO\Http\CurlRequest;
use TSK\SSO\ThirdParty\VendorConnection;
use TSK\SSO\ThirdParty\VendorConnectionFactory;

/**
 * @package TSK\SSO\ThirdParty\Microsoft
 */
class MicrosoftConnectionFactory implements VendorConnectionFactory
{
    /**
     * Returns a Microsoft Connection instance using given credentials.
     * Visit the following link for credentials or to add an app.
     * @see https://apps.dev.microsoft.com/#/appList
     *
     * @param string $clientId the client id which can be generated at the third party portal
     * @param string $clientSecret this can be found similar to the clientId
     * @param string $callbackUrl the url to callback after a third party auth attempt
     * @return VendorConnection
     */
    public function get($clientId, $clientSecret, $callbackUrl)
    {
        return new MicrosoftConnection(
            new MicrosoftApiConfiguration(
                $clientId,
                $clientSecret,
                $callbackUrl
            ),
            new CurlRequest()
        );
    }
}
