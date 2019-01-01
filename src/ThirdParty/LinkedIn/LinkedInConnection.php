<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 31-12-2018
 */

namespace TSK\SSO\ThirdParty\LinkedIn;

use TSK\SSO\Http\CurlRequest;
use TSK\SSO\ThirdParty;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\ThirdPartyUser;
use TSK\SSO\ThirdParty\VendorConnection;

/**
 * @codeCoverageIgnore
 * @package TSK\SSO\ThirdParty\LinkedIn
 * @see https://developer.linkedin.com/docs/oauth2
 */
class LinkedInConnection implements VendorConnection
{
    const AUTH_API_BASE = 'https://www.linkedin.com/oauth/v2';
    const API_BASE = 'https://api.linkedin.com/v1';

    /**
     * @var LinkedInApiConfiguration
     */
    private $linkedInApiConfiguration;

    /**
     * @var CurlRequest
     */
    private $curlClient;

    /**
     * @param LinkedInApiConfiguration $linkedInApiConfiguration
     * @param CurlRequest $curlClient
     */
    public function __construct(
        LinkedInApiConfiguration $linkedInApiConfiguration,
        CurlRequest $curlClient
    ) {
        $this->linkedInApiConfiguration = $linkedInApiConfiguration;
        $this->curlClient = $curlClient;
    }

    /**
     * Use this to get a link to redirect a user to the facebook login page.
     *
     * @return string
     */
    public function getGrantUrl()
    {
        return sprintf(
            '%s/authorization?response_type=code&client_id=%s&redirect_uri=%s&state=%s&scope=%s',
            self::AUTH_API_BASE,
            $this->linkedInApiConfiguration->appId(),
            $this->linkedInApiConfiguration->redirectUrl(),
            $this->linkedInApiConfiguration->ourSecretState(),
            $this->linkedInApiConfiguration->appPermissions()
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
        // state and code validation
        if (empty($_GET['code'])
            || empty($_GET['state'])
            || $_GET['state'] !== $this->linkedInApiConfiguration->ourSecretState()
        ) {
            throw new ThirdPartyConnectionFailedException('Invalid request!');
        }

        $accessTokenJsonInfo = $this->curlClient->post(
            sprintf(
                "%s/accessToken?grant_type=authorization_code&code=%s&redirect_uri=%s&client_id=%s&client_secret=%s",
                self::AUTH_API_BASE,
                $_GET['code'],
                $this->linkedInApiConfiguration->redirectUrl(),
                $this->linkedInApiConfiguration->appId(),
                $this->linkedInApiConfiguration->appSecret()
            )
        );

        $accessTokenInfo = json_decode($accessTokenJsonInfo, true);
        if (!empty($accessTokenInfo['error'])) {
            throw new ThirdPartyConnectionFailedException(
                'Failed to establish a new third party vendor connection due to' . $accessTokenInfo['error_description']
            );
        }

        return new CommonAccessToken($accessTokenInfo['access_token'], ThirdParty::LINKEDIN);
    }

    /**
     * Get the current authenticated user data using there existing granted token
     *
     * @param CommonAccessToken $accessToken
     * @return ThirdPartyUser
     * @throws NoThirdPartyEmailFoundException
     */
    public function getSelf(CommonAccessToken $accessToken)
    {
        $userJson = $this->curlClient->get(sprintf(
            '%s/people/~:(id,firstName,lastName,emailAddress)?format=json&oauth2_access_token=%s',
            self::API_BASE,
            $accessToken->token()
        ));

        $user = json_decode($userJson, true);
        if (empty($user['emailAddress'])) {
            throw new NoThirdPartyEmailFoundException('An email address cannot be found from vendor');
        }

        return new ThirdPartyUser(
            $user['id'],
            sprintf('%s %s', $user['firstName'], $user['lastName']),
            $user['emailAddress']
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
        $this->curlClient->get(sprintf(
            'https://api.linkedin.com/uas/oauth/invalidateToken?oauth2_access_token=%s',
            $accessToken->token()
        ));

        return true;
    }
}
