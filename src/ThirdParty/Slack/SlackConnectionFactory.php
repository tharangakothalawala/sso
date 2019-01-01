<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 31-12-2018
 */

namespace TSK\SSO\ThirdParty\Slack;

use TSK\SSO\ThirdParty\VendorConnection;
use TSK\SSO\ThirdParty\VendorConnectionFactory;
use TSK\SSO\Http\CurlRequest;

/**
 * @package TSK\SSO\ThirdParty\Slack
 */
class SlackConnectionFactory implements VendorConnectionFactory
{
    const DEFAULT_PERMISSIONS = 'identity.basic,identity.email,identity.team,identity.avatar';

    /**
     * @var string
     */
    private $permissions;

    public function __construct($permissions = self::DEFAULT_PERMISSIONS)
    {
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
        return new SlackConnection(
            new SlackApiConfiguration(
                $clientId,
                $clientSecret,
                $this->permissions,
                $callbackUrl
            ),
            new CurlRequest()
        );
    }
}
