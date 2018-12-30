<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 */

namespace TSK\SSO\ThirdParty\Google;

use TSK\SSO\Http\CurlRequest;
use TSK\SSO\ThirdParty;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\ThirdPartyUser;
use TSK\SSO\ThirdParty\VendorConnection;
use Google_Client;
use Google_Service_Plus;
use RuntimeException;

/**
 * @package TSK\SSO\ThirdParty\Google
 * @see https://console.developers.google.com
 */
class GoogleConnection implements VendorConnection
{
    const API_BASE = 'https://www.googleapis.com';

    /**
     * @var GoogleApiConfiguration
     */
    private $apiConfiguration;

    /**
     * @var CurlRequest
     */
    private $curlClient;

    /**
     * @var Google_Client
     */
    private $googleClient;

    /**
     * @param GoogleApiConfiguration $apiConfiguration
     * @param CurlRequest $curlClient
     */
    public function __construct(
        GoogleApiConfiguration $apiConfiguration,
        CurlRequest $curlClient
    ) {
        $this->apiConfiguration = $apiConfiguration;
        $this->curlClient = $curlClient;

        $this->googleClient = new Google_Client(array(
            'client_id' => $this->apiConfiguration->appId(),
            'client_secret' => $this->apiConfiguration->appSecret(),
            'redirect_uri' => $this->apiConfiguration->redirectUrl(),
        ));
        $this->googleClient->setScopes($this->apiConfiguration->appPermissions());
    }

    /**
     * Use this to get a link to redirect a user to the google login page.
     *
     * @return string
     */
    public function getGrantUrl()
    {
        return filter_var($this->googleClient->createAuthUrl(), FILTER_SANITIZE_URL);
    }

    /**
     * Grants a new access token
     *
     * @return CommonAccessToken
     * @throws ThirdPartyConnectionFailedException
     */
    public function grantNewAccessToken()
    {
        if (empty($_GET['code'])) {
            throw new RuntimeException('Error : A code cannot be found!');
        }

        try {
            $authResponse = $this->googleClient->fetchAccessTokenWithAuthCode($_GET['code']);
            $accessTokenInfo = $this->googleClient->getAccessToken();
            $userInfo = array();
            if ($accessTokenInfo) {
                $userInfo = $this->googleClient->verifyIdToken();
            }
        } catch (\Exception $ex) {
            throw new ThirdPartyConnectionFailedException(sprintf('Failed to establish a new third party vendor connection. Error : %s', $ex->getMessage()));
        }

        if (empty($authResponse['access_token'])) {
            throw new ThirdPartyConnectionFailedException('Failed to establish a new third party vendor connection.');
        }

        return new CommonAccessToken(
            $authResponse['access_token'],
            ThirdParty::GOOGLE,
            $userInfo['email']
        );
    }

    /**
     * Get the current authenticated user data using there existing granted token
     *
     * @param CommonAccessToken $accessToken
     * @return ThirdPartyUser
     * @throws NoThirdPartyEmailFoundException
     * @throws ThirdPartyConnectionFailedException
     */
    public function getSelf(CommonAccessToken $accessToken)
    {
        $userJsonInfo = $this->curlClient->get(
            sprintf('%s/oauth2/v1/userinfo?access_token=%s', self::API_BASE, $accessToken->token())
        );
        $userInfo = json_decode($userJsonInfo, true);
        if (!empty($userInfo['error'])) {
            throw new ThirdPartyConnectionFailedException(sprintf('Failed to retrieve third party user data. Error : %s', $userInfo['error']));
        }

        if (empty($userInfo['email'])) {
            throw new NoThirdPartyEmailFoundException('An email address cannot be found from vendor');
        }

        return new ThirdPartyUser(
            $userInfo['id'],
            $userInfo['name'],
            $userInfo['email'],
            !empty($userInfo['picture']) ? $userInfo['picture'] : '',
            !empty($userInfo['gender']) ? $userInfo['gender'] : ''
        );
    }

    /**
     * Use this to revoke the access to the third party data. this will completely remove the access from the vendor side.
     *
     * @param CommonAccessToken $accessToken
     * @return bool
     */
    public function revokeAccess(CommonAccessToken $accessToken)
    {
        $revoked = $this->googleClient->revokeToken($accessToken->token());
        if (!$revoked) {
            return false;
        }

        return true;
    }
}
