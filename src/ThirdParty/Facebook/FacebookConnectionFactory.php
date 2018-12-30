<?php
/**
 * @author : Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   : 30-12-2018
 */

namespace TSK\SSO\ThirdParty\Facebook;

use TSK\SSO\ThirdParty\VendorConnectionFactory;

/**
 * @package TSK\SSO\ThirdParty\Facebook
 */
class FacebookConnectionFactory implements VendorConnectionFactory
{
    private $defaultGraphVersion;
    private $permissions;

    public function __construct($defaultGraphVersion = 'v2.12', $permissions = 'public_profile,email')
    {
        $this->defaultGraphVersion = $defaultGraphVersion;
        $this->permissions = $permissions;
    }

    /**
     * @param string $clientId the client id which can be generated at the third party portal
     * @param string $clientSecret this can be found similar to the clientId
     * @param string $callbackUrl the url to callback after a third party auth attempt
     * @return VendorConnection
     */
    public function get($clientId, $clientSecret, $callbackUrl)
    {
        return new FacebookConnection(
            new FacebookApiConfiguration(
                $this->defaultGraphVersion,
                $clientId,
                $clientSecret,
                $this->permissions,
                $callbackUrl
            ),
            new ThirdPartyConnectionRepository()
        );
    }
}
