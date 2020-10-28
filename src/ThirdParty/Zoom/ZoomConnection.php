<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   28-10-2020
 */

namespace TSK\SSO\ThirdParty\Zoom;

use TSK\SSO\Http\CurlRequest;
use TSK\SSO\ThirdParty;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\ThirdPartyUser;
use TSK\SSO\ThirdParty\VendorConnection;

/**
 * @codeCoverageIgnore
 * @package TSK\SSO\ThirdParty\Zoom
 * @see     https://marketplace.zoom.us/docs/guides/auth/oauth
 */
class ZoomConnection implements VendorConnection
{
    const API_BASE = 'https://zoom.us';

    /**
     * @var ZoomApiConfiguration
     */
    private $apiConfiguration;

    /**
     * @var CurlRequest
     */
    private $curlClient;

    /**
     * @param ZoomApiConfiguration $apiConfiguration
     * @param CurlRequest          $curlClient
     */
    public function __construct(ZoomApiConfiguration $apiConfiguration, CurlRequest $curlClient)
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
            '%s/oauth/authorize?response_type=code&client_id=%s&redirect_uri=%s&state=%s',
            self::API_BASE,
            $this->apiConfiguration->clientId(),
            urldecode($this->apiConfiguration->redirectUrl()),
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

        $accessTokenJsonInfo = $this->curlClient->postUrlEncoded(
            sprintf("%s/oauth/token", self::API_BASE),
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
            )
        );

        $accessTokenInfo = json_decode($accessTokenJsonInfo, true);
        if (empty($accessTokenInfo['access_token'])) {
            throw new ThirdPartyConnectionFailedException(
                'An error occurred while getting the access.'
            );
        }

        return new CommonAccessToken($accessTokenInfo['access_token'], ThirdParty::ZOOM);
    }

    /**
     * Use this to retrieve the current user's third party user data using there existing granted access token
     *
     * @param CommonAccessToken $accessToken
     *
     * @return ThirdPartyUser
     * @throws NoThirdPartyEmailFoundException
     * @throws ThirdPartyConnectionFailedException
     */
    public function getSelf(CommonAccessToken $accessToken)
    {
        $userJsonInfo = $this->curlClient->get(
            'https://api.zoom.us/v2/users/me',
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
            sprintf('%s %s', $userInfo['first_name'], $userInfo['last_name']),
            $userInfo['email'],
            $userInfo['pic_url']
        );
    }

    /**
     * Use this to revoke the access to the third party data.
     * This will completely remove the access from the vendor side.
     *
     * @param CommonAccessToken $accessToken
     *
     * @return bool
     */
    public function revokeAccess(CommonAccessToken $accessToken)
    {
        $revokeJsonInfo = $this->curlClient->post(
            sprintf(
                "%s/oauth/revoke?token=%s",
                self::API_BASE,
                $accessToken->token()
            ),
            array(),
            array(
                'Authorization' => sprintf(
                    'Basic %s',
                    base64_encode(
                        sprintf('%s:%s', $this->apiConfiguration->clientId(), $this->apiConfiguration->clientSecret())
                    )
                ),
            )
        );

        $revokeInfo = json_decode($revokeJsonInfo, true);
        return (!empty($revokeInfo['status']) && $revokeInfo['status'] === 'success');
    }
}
