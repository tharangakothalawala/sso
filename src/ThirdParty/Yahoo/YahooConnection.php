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
    /**
     * @var YahooApiConfiguration
     */
    private $configuration;

    /**
     * @var CurlRequest
     */
    private $curl;

    /**
     * YahooConnection constructor.
     * @param YahooApiConfiguration $configuration
     * @param CurlRequest $curl
     */
    public function __construct(YahooApiConfiguration $configuration, CurlRequest $curl)
    {
        $this->configuration = $configuration;
        $this->curl = $curl;
    }

    /**
     * Use this to get a link to redirect a user to the third party login
     *
     * @return string|null
     */
    public function getGrantUrl()
    {
        return sprintf(
            'https://api.login.yahoo.com/oauth2/request_auth?client_id=%s&redirect_uri=%s&response_type=code',
            $this->configuration->clientId(),
            $this->configuration->redirectUrl()
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
        if (empty($_GET['code'])) {
            throw new ThirdPartyConnectionFailedException('Invalid request!');
        }

        // @TODO
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
        // @TODO
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
        // @TODO
    }
}
