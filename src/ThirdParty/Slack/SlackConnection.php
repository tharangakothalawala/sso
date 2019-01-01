<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 31-12-2018
 */

namespace TSK\SSO\ThirdParty\Slack;

use TSK\SSO\Http\CurlRequest;
use TSK\SSO\ThirdParty;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\ThirdPartyUser;
use TSK\SSO\ThirdParty\VendorConnection;

/**
 * @codeCoverageIgnore
 * @package TSK\SSO\ThirdParty\Slack
 * @see https://api.slack.com/docs/oauth#flow
 */
class SlackConnection implements VendorConnection
{
    const API_BASE = 'https://slack.com';

    /**
     * @var SlackApiConfiguration
     */
    private $apiConfiguration;

    /**
     * @var CurlRequest
     */
    private $curlClient;

    /**
     * @param SlackApiConfiguration $apiConfiguration
     * @param CurlRequest $curlClient
     */
    public function __construct(SlackApiConfiguration $apiConfiguration, CurlRequest $curlClient)
    {
        $this->apiConfiguration = $apiConfiguration;
        $this->curlClient = $curlClient;
    }

    /**
     * Use this to redirect the user to the third party login page to grant permission to use their account.
     */
    public function getGrantUrl()
    {
        return sprintf(
            '%s/oauth/authorize?scope=%s&client_id=%s&redirect_uri=%s&state=%s',
            self::API_BASE,
            $this->apiConfiguration->appPermissions(),
            $this->apiConfiguration->appId(),
            $this->apiConfiguration->redirectUrl(),
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

        $accessTokenJsonInfo = $this->curlClient->get(sprintf(
            "%s/api/oauth.access?client_id=%s&client_secret=%s&code=%s",
            self::API_BASE,
            $this->apiConfiguration->appId(),
            $this->apiConfiguration->appSecret(),
            $_GET['code']
        ));

        $accessTokenInfo = json_decode($accessTokenJsonInfo, true);
        if (!empty($accessTokenInfo['error'])) {
            throw new ThirdPartyConnectionFailedException(
                'An error occurred while getting the access. Details : ' . $accessTokenInfo['error']
            );
        }

        return new CommonAccessToken($accessTokenInfo['access_token'], ThirdParty::SLACK);
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
        $userJsonInfo = $this->curlClient->get(sprintf(
            "%s/api/users.identity?token=%s",
            self::API_BASE,
            $accessToken->token()
        ));

        $userInfo = json_decode($userJsonInfo, true);
        if (!empty($userInfo['error'])) {
            throw new ThirdPartyConnectionFailedException('Error occured : ' . $userInfo['error']);
        }

        if (empty($userInfo['user']['email'])) {
            throw new NoThirdPartyEmailFoundException('An email address cannot be found from vendor');
        }

        return new ThirdPartyUser(
            $userInfo['user']['id'],
            $userInfo['user']['name'],
            $userInfo['user']['email'],
            $userInfo['user']['image_1024']
        );
    }

    /**
     * Use this to revoke the access to the third party data.
     * This will completely remove the access from the vendor side.
     *
     * @param CommonAccessToken $accessToken
     * @return bool
     */
    public function revokeAccess(CommonAccessToken $accessToken)
    {
        $revokeJsonInfo = $this->curlClient->get(sprintf(
            "%s/api/auth.revoke?token=%s",
            self::API_BASE,
            $accessToken->token()
        ));

        $revokeInfo = json_decode($revokeJsonInfo, true);
        return (!empty($revokeInfo['revoked']) && boolval($revokeInfo['revoked']) === true);
    }
}
