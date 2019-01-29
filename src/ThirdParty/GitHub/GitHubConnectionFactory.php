<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 29-01-2019
 */

namespace TSK\SSO\ThirdParty\GitHub;

use TSK\SSO\Http\CurlRequest;
use TSK\SSO\ThirdParty\VendorConnection;
use TSK\SSO\ThirdParty\VendorConnectionFactory;

/**
 * @package TSK\SSO\ThirdParty\GitHub
 */
class GitHubConnectionFactory implements VendorConnectionFactory
{
    /**
     * @var string
     */
    private $oauthAppName;

    /**
     * @param string $oauthAppName
     */
    public function __construct($oauthAppName)
    {
        $this->oauthAppName = $oauthAppName;
    }

    /**
     * Returns a GitHub Connection instance using given credentials. Visit the following link for credentials.
     * @see https://github.com/settings/applications/new
     *
     * @param string $clientId the client id which can be generated at the third party portal
     * @param string $clientSecret this can be found similar to the clientId
     * @param string $callbackUrl the url to callback after a third party auth attempt
     * @return VendorConnection
     */
    public function get($clientId, $clientSecret, $callbackUrl)
    {
        return new GitHubConnection(
            new GitHubApiConfiguration(
                $clientId,
                $clientSecret,
                $callbackUrl,
                $this->oauthAppName
            ),
            new CurlRequest()
        );
    }
}
