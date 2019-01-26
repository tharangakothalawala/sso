<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 22-01-2019
 */

namespace TSK\SSO\ThirdParty\Yahoo;

use TSK\SSO\Http\CurlRequest;
use TSK\SSO\ThirdParty;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\ThirdPartyUser;
use TSK\SSO\ThirdParty\VendorConnection;

/**
 * @codeCoverageIgnore
 * @package TSK\SSO\ThirdParty\Yahoo
 * @see https://developer.yahoo.com/oauth2/guide/flows_authcode
 */
class YahooConnection implements VendorConnection
{
    const API_BASE = 'https://api.login.yahoo.com/oauth2';

    /**
     * @var YahooApiConfiguration
     */
    private $apiConfiguration;

    /**
     * @var CurlRequest
     */
    private $curlClient;

    /**
     * YahooConnection constructor.
     * @param YahooApiConfiguration $apiConfiguration
     * @param CurlRequest $curlClient
     */
    public function __construct(YahooApiConfiguration $apiConfiguration, CurlRequest $curlClient)
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
            '%s/request_auth?client_id=%s&redirect_uri=%s&response_type=code&language=en-us&state=%s',
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

        $accessTokenJsonInfo = $this->curlClient->postRaw(
            sprintf("%s/get_token", self::API_BASE),
            sprintf(
                'client_id=%s&client_secret=%s&grant_type=authorization_code&redirect_uri=%s&code=%s',
                $this->apiConfiguration->clientId(),
                $this->apiConfiguration->clientSecret(),
                urlencode($this->apiConfiguration->redirectUrl()),
                $_GET['code']
            ),
            array(
                'Authorization' => sprintf(
                    'Basic %s',
                    base64_encode(
                        sprintf('%s:%s', $this->apiConfiguration->clientId(), $this->apiConfiguration->clientSecret())
                    )
                ),
                'Content-Type' => 'application/x-www-form-urlencoded',
            )
        );

        $accessTokenJson = json_decode($accessTokenJsonInfo, true);
        if (empty($accessTokenJson['access_token'])) {
            throw new ThirdPartyConnectionFailedException('Failed to establish a new third party vendor connection.');
        }

        return new CommonAccessToken(
            $accessTokenJson['access_token'],
            ThirdParty::YAHOO
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
        $userJsonInfo = $this->curlClient->yahooGet(
            'https://social.yahooapis.com/v1/user/me/profile?format=json',
            array(
                sprintf('Authorization: Bearer %s', $accessToken->token()),
                'Accept: application/json',
            )
        );

        $userInfo = json_decode($userJsonInfo, true);
        if (empty($userInfo['profile']['emails'][0]['handle'])) {
            throw new NoThirdPartyEmailFoundException('An email address cannot be found from vendor');
        }

        return new ThirdPartyUser(
            $userInfo['profile']['guid'],
            sprintf('%s %s', $userInfo['profile']['givenName'], $userInfo['profile']['familyName']),
            $userInfo['profile']['emails'][0]['handle'],
            !empty($userInfo['profile']['image']['imageUrl']) ? $userInfo['profile']['image']['imageUrl'] : '',
            !empty($userInfo['profile']['gender']) ? $userInfo['profile']['gender'] : ''
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
        // noop : cannot find documentation on how to revoke the app's access.
        return true;
    }
}
