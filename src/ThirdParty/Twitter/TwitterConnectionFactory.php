<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 13-01-2019
 */

namespace TSK\SSO\ThirdParty\Twitter;

use TSK\SSO\ThirdParty\VendorConnection;
use TSK\SSO\ThirdParty\VendorConnectionFactory;

/**
 * @package TSK\SSO\ThirdParty\Twitter
 */
class TwitterConnectionFactory implements VendorConnectionFactory
{
    /**
     * Returns a Twitter Connection instance using given credentials. Visit the following link for credentials.
     * @see https://developer.twitter.com/en/apps
     *
     * @param string $clientId the client id which can be generated at the third party portal
     * @param string $clientSecret this can be found similar to the clientId
     * @param string $callbackUrl the url to callback after a third party auth attempt
     * @return VendorConnection
     */
    public function get($clientId, $clientSecret, $callbackUrl)
    {
        return new TwitterConnection(
            new TwitterApiConfiguration(
                $clientId,
                $clientSecret,
                $callbackUrl
            )
        );
    }
}
