<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 02-02-2019
 */

namespace TSK\SSO\ThirdParty\Microsoft;

use TSK\SSO\Http\CurlRequest;
use TSK\SSO\ThirdParty;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\ThirdPartyUser;
use TSK\SSO\ThirdParty\VendorConnection;

/**
 * @codeCoverageIgnore
 * @package TSK\SSO\ThirdParty\Microsoft
 * @see https://docs.microsoft.com/en-us/azure/active-directory/develop/v1-protocols-oauth-code
 */
class MicrosoftConnection implements VendorConnection
{
    const API_BASE = 'https://login.microsoftonline.com';

    /**
     * @var MicrosoftApiConfiguration
     */
    private $apiConfiguration;

    /**
     * @var CurlRequest
     */
    private $curlClient;

    /**
     * MicrosoftConnection constructor.
     * @param MicrosoftApiConfiguration $apiConfiguration
     * @param CurlRequest $curlClient
     */
    public function __construct(MicrosoftApiConfiguration $apiConfiguration, CurlRequest $curlClient)
    {
        $this->apiConfiguration = $apiConfiguration;
        $this->curlClient = $curlClient;
    }

    /**
     * Use this to get a link to redirect a user to the third party login
     *
     * @return string|null
     */
    public function getGrantUrl()
    {
        return sprintf(
            '%s/common/oauth2/authorize?client_id=%s&redirect_uri=%s&response_type=code&response_mode=query&state=%s',
            self::API_BASE,
            $this->apiConfiguration->clientId(),
            urlencode($this->apiConfiguration->redirectUrl()),
            $this->apiConfiguration->ourSecretState()
        );
    }

    /**
     * Grants a new access token
     *
     * @return CommonAccessToken
     * @throws ThirdPartyConnectionFailedException
     */
    public function grantNewAccessToken()
    {
        if (empty($_GET['code'])
            || empty($_GET['state'])
            || $_GET['state'] !== $this->apiConfiguration->ourSecretState()
        ) {
            throw new ThirdPartyConnectionFailedException('Invalid request!');
        }

        var_dump($_GET);exit;
    }

    /**
     * Use this to retrieve the current user's third party user data using there existing granted access token
     *
     * @param CommonAccessToken $accessToken
     * @return ThirdPartyUser
     * @throws NoThirdPartyEmailFoundException
     * @throws ThirdPartyConnectionFailedException
     */
    public function getSelf(CommonAccessToken $accessToken)
    {
        // TODO
    }

    /**
     * Use this to revoke the access to the third party data.
     * This will completely remove the access from the vendor side.
     * @see https://developer.github.com/v3/oauth_authorizations/#revoke-a-grant-for-an-application
     *
     * @param CommonAccessToken $accessToken
     * @return bool
     */
    public function revokeAccess(CommonAccessToken $accessToken)
    {
        // TODO
    }
}
