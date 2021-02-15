<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   15-02-2021
 */

namespace TSK\SSO\ThirdParty\Stripe;

use TSK\SSO\ThirdParty\VendorConnection;
use TSK\SSO\ThirdParty\VendorConnectionFactory;

/**
 * @package TSK\SSO\ThirdParty\Spotify
 */
class StripeConnectionFactory implements VendorConnectionFactory
{
    /**
     * Returns a Stripe Connection instance using given credentials. Visit the following links for credentials.
     * @see https://dashboard.stripe.com/apikeys
     * @see https://dashboard.stripe.com/settings/applications
     *
     * @param string $clientId     the client id which can be generated at the Stripe portal
     * @param string $clientSecret this can be found similar to the clientId
     * @param string $callbackUrl  the url to callback after a third party auth attempt
     *
     * @return VendorConnection
     */
    public function get($clientId, $clientSecret, $callbackUrl)
    {
        return new StripeConnection(
            new StripeConfiguration(
                $clientId,
                $clientSecret,
                $callbackUrl
            )
        );
    }
}
