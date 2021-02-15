<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   15-02-2021
 */

namespace TSK\SSO\ThirdParty\Stripe;

use Stripe\Account;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\OAuth\OAuthErrorException;
use Stripe\OAuth;
use TSK\SSO\ThirdParty;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\ThirdPartyUser;
use TSK\SSO\ThirdParty\VendorConnection;

/**
 * @codeCoverageIgnore
 * @package TSK\SSO\ThirdParty\Stripe
 * @see     https://stripe.com/docs/connect/oauth-standard-accounts
 */
class StripeConnection implements VendorConnection
{
    /**
     * @var StripeConfiguration
     */
    private $configuration;

    /**
     * StripeConnectConnection constructor.
     *
     * @param StripeConfiguration $configuration
     */
    public function __construct(StripeConfiguration $configuration)
    {
        if (class_exists('\Stripe\Stripe')) {
            \Stripe\Stripe::setApiKey($configuration->clientSecret());
        }
        $this->configuration = $configuration;
    }

    /**
     * Use this to get a link to redirect a user to the third party login
     *
     * @return string|null
     */
    public function getGrantUrl()
    {
        return sprintf(
            "https://connect.stripe.com/oauth/authorize?response_type=code&client_id=%s&scope=read_write&state=%s&redirect_uri=%s",
            $this->configuration->clientId(),
            $this->configuration->ourSecretState(),
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
        if (empty($_GET['code'])
            || empty($_GET['state'])
            || $_GET['state'] !== $this->configuration->ourSecretState()
        ) {
            throw new ThirdPartyConnectionFailedException('Invalid request!');
        }

        try {
            $response = OAuth::token([
                'grant_type' => 'authorization_code',
                'code' => $_GET['code'],
            ]);
        } catch (OAuthErrorException $ex) {
            throw new ThirdPartyConnectionFailedException($ex->getMessage(), $ex->getCode(), $ex);
        }

        return new CommonAccessToken(
            $response->access_token,
            ThirdParty::STRIPE,
            $response->stripe_user_id
        );
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
        try {
            $account = Account::retrieve($accessToken->email());
        } catch (ApiErrorException $ex) {
            throw new ThirdPartyConnectionFailedException($ex->getMessage(), $ex->getCode(), $ex);
        }

        if (empty($account->id)) {
            throw new NoThirdPartyEmailFoundException("Stripe account and/or email address cannot be retrieved");
        }

        return new ThirdPartyUser(
            $account->id,
            !empty($account->business_profile->name) ? $account->business_profile->name : '',
            !empty($account->email) ? $account->email : ''
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
        try {
            OAuth::deauthorize([
                'client_id' => $this->configuration->clientId(),
                'stripe_user_id' => $accessToken->email(),
            ]);
        } catch (OAuthErrorException $ex) {
            return false;
        }

        return true;
    }
}
