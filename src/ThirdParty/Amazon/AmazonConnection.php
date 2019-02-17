<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 17-02-2019
 */

namespace TSK\SSO\ThirdParty\Amazon;

use TSK\SSO\Http\CurlRequest;
use TSK\SSO\ThirdParty;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\ThirdPartyUser;
use TSK\SSO\ThirdParty\VendorConnection;

/**
 * @codeCoverageIgnore
 * @package TSK\SSO\ThirdParty\Amazon
 * @see https://login.amazon.com/website
 * @see https://images-na.ssl-images-amazon.com/images/G/01/lwa/dev/docs/website-developer-guide._TTH_.pdf
 */
class AmazonConnection implements VendorConnection
{
    const API_BASE = 'https://api.amazon.com';

    /**
     * @var AmazonApiConfiguration
     */
    private $apiConfiguration;

    /**
     * @var CurlRequest
     */
    private $curlClient;

    /**
     * AmazonConnection constructor.
     * @param AmazonApiConfiguration $apiConfiguration
     * @param CurlRequest $curlClient
     */
    public function __construct(AmazonApiConfiguration $apiConfiguration, CurlRequest $curlClient)
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
            'https://www.amazon.com/ap/oa?client_id=%s&scope=profile&response_type=code&redirect_uri=%s&state=%s',
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

        $accessTokenJsonResponse = $this->curlClient->postUrlEncoded(
            sprintf("%s/auth/o2/token", self::API_BASE),
            sprintf(
                'grant_type=authorization_code&code=%s&client_id=%s&client_secret=%s&redirect_uri=%s',
                $_GET['code'],
                $this->apiConfiguration->clientId(),
                $this->apiConfiguration->clientSecret(),
                urlencode($this->apiConfiguration->redirectUrl())
            )
        );

        $accessTokenData = json_decode($accessTokenJsonResponse, true);
        if (empty($accessTokenData['access_token'])) {
            throw new ThirdPartyConnectionFailedException('Failed to establish a new third party vendor connection.');
        }

        return new CommonAccessToken(
            $accessTokenData['access_token'],
            ThirdParty::AMAZON
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
            sprintf("%s/user/profile", self::API_BASE),
            array(
                sprintf('Authorization: Bearer %s', $accessToken->token()),
                'Accept: application/json',
                'Accept-Language: en-us',
            )
        );

        $userInfo = json_decode($userJsonInfo, true);
        if (empty($userInfo['email'])) {
            throw new NoThirdPartyEmailFoundException('An email address cannot be found from vendor');
        }

        return new ThirdPartyUser(
            $userInfo['user_id'],
            $userInfo['name'],
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
