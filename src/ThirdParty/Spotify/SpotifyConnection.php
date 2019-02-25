<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 25-02-2019
 */

namespace TSK\SSO\ThirdParty\Spotify;

use TSK\SSO\Http\CurlRequest;
use TSK\SSO\ThirdParty;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\ThirdPartyUser;
use TSK\SSO\ThirdParty\VendorConnection;

/**
 * @codeCoverageIgnore
 * @package TSK\SSO\ThirdParty\Spotify
 * @see https://developer.spotify.com/documentation/general/guides/authorization-guide/
 */
class SpotifyConnection implements VendorConnection
{
    const API_BASE = 'https://accounts.spotify.com';

    /**
     * @var SpotifyApiConfiguration
     */
    private $apiConfiguration;

    /**
     * @var CurlRequest
     */
    private $curlClient;

    /**
     * SpotifyConnection constructor.
     * @param SpotifyApiConfiguration $apiConfiguration
     * @param CurlRequest $curlClient
     */
    public function __construct(SpotifyApiConfiguration $apiConfiguration, CurlRequest $curlClient)
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
            '%s/authorize?client_id=%s&scope=%s&response_type=code&redirect_uri=%s&state=%s',
            self::API_BASE,
            $this->apiConfiguration->clientId(),
            urlencode('user-read-email'),
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

        $accessTokenJsonResponse = $this->curlClient->postUrlEncoded(
            sprintf("%s/api/token", self::API_BASE),
            sprintf(
                'client_id=%s&client_secret=%s&grant_type=authorization_code&redirect_uri=%s&code=%s',
                $this->apiConfiguration->clientId(),
                $this->apiConfiguration->clientSecret(),
                urlencode($this->apiConfiguration->redirectUrl()),
                $_GET['code']
            )
        );

        $accessTokenData = json_decode($accessTokenJsonResponse, true);
        if (empty($accessTokenData['access_token'])) {
            throw new ThirdPartyConnectionFailedException('Failed to establish a new third party vendor connection.');
        }

        return new CommonAccessToken(
            $accessTokenData['access_token'],
            ThirdParty::SPOTIFY
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
            'https://api.spotify.com/v1/me',
            array(
                sprintf('Authorization: Bearer %s', $accessToken->token()),
            )
        );

        $userInfo = json_decode($userJsonInfo, true);
        if (empty($userInfo['email'])) {
            throw new NoThirdPartyEmailFoundException('An email address cannot be found from vendor');
        }

        return new ThirdPartyUser(
            $userInfo['id'],
            $userInfo['display_name'],
            $userInfo['email']
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
