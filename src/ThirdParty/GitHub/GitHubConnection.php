<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 29-01-2019
 */

namespace TSK\SSO\ThirdParty\GitHub;

use TSK\SSO\Http\CurlRequest;
use TSK\SSO\ThirdParty;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\ThirdPartyUser;
use TSK\SSO\ThirdParty\VendorConnection;

/**
 * @codeCoverageIgnore
 * @package TSK\SSO\ThirdParty\GitHub
 * @see https://developer.github.com/apps/building-oauth-apps/authorizing-oauth-apps
 */
class GitHubConnection implements VendorConnection
{
    const API_BASE = 'https://github.com/login/oauth';

    /**
     * @var GitHubApiConfiguration
     */
    private $apiConfiguration;

    /**
     * @var CurlRequest
     */
    private $curlClient;

    /**
     * GitHubConnection constructor.
     * @param GitHubApiConfiguration $apiConfiguration
     * @param CurlRequest $curlClient
     */
    public function __construct(GitHubApiConfiguration $apiConfiguration, CurlRequest $curlClient)
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
            '%s/authorize?client_id=%s&redirect_uri=%s&response_type=code&language=en-us&state=%s',
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

        $accessTokenUrlQueryString = $this->curlClient->post(
            sprintf("%s/access_token", self::API_BASE),
            array(
                'client_id' => $this->apiConfiguration->clientId(),
                'client_secret' => $this->apiConfiguration->clientSecret(),
                'code' => $_GET['code'],
                'redirect_uri' => $this->apiConfiguration->redirectUrl()
            )
        );

        parse_str($accessTokenUrlQueryString, $queryStringArray);
        if (empty($queryStringArray['access_token'])) {
            throw new ThirdPartyConnectionFailedException('Failed to establish a new third party vendor connection.');
        }

        return new CommonAccessToken(
            $queryStringArray['access_token'],
            ThirdParty::GITHUB
        );
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
        $userJsonInfo = $this->curlClient->get(
            'https://api.github.com/user',
            array(
                sprintf('Authorization: token %s', $accessToken->token()),
                sprintf('User-Agent: %s', $this->apiConfiguration->oauthAppName()),
                'Accept: application/json',
            )
        );

        $userInfo = json_decode($userJsonInfo, true);
        if (empty($userInfo['email'])) {
            throw new NoThirdPartyEmailFoundException('An email address cannot be found from vendor');
        }

        return new ThirdPartyUser(
            $userInfo['login'],
            $userInfo['name'],
            $userInfo['email'],
            !empty($userInfo['avatar_url']) ? $userInfo['avatar_url'] : '',
            !empty($userInfo['profile']['gender']) ? $userInfo['profile']['gender'] : ''
        );
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
        $response = $this->curlClient->deleteWithBasicAuth(
            sprintf(
                'https://api.github.com/applications/%s/grants/%s',
                $this->apiConfiguration->clientId(),
                $accessToken->token()
            ),
            sprintf('%s:%s', $this->apiConfiguration->clientId(), $this->apiConfiguration->clientSecret()),
            array(sprintf('User-Agent: %s', $this->apiConfiguration->oauthAppName()))
        );

        // on success returns an empty string
        return empty($response);
    }
}
