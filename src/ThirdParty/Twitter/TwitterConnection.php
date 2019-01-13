<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 13-01-2019
 */

namespace TSK\SSO\ThirdParty\Twitter;

use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;
use TSK\SSO\ThirdParty;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\ThirdPartyUser;
use TSK\SSO\ThirdParty\VendorConnection;

/**
 * @codeCoverageIgnore
 * @package TSK\SSO\ThirdParty\Twitter
 * @see https://developer.twitter.com/en/docs/twitter-for-websites/log-in-with-twitter/guides/implementing-sign-in-with-twitter
 */
class TwitterConnection implements VendorConnection
{
    const TOKEN_SEPARATOR = '::';

    /**
     * @var TwitterApiConfiguration
     */
    private $configuration;

    /**
     * @var TwitterOAuth
     */
    private $twitter;

    /**
     * TwitterConnection constructor.
     * @param TwitterApiConfiguration $configuration
     */
    public function __construct(TwitterApiConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->twitter = $this->getTwitter();
    }

    /**
     * Use this to get a link to redirect a user to the third party login
     *
     * @return string|null
     */
    public function getGrantUrl()
    {
        try {
            $response = $this->twitter->oauth('oauth/request_token', array(
                'oauth_callback' => $this->configuration->redirectUrl()
            ));
        } catch (TwitterOAuthException $ex) {
            return null;
        }

        return sprintf('%s/oauth/authenticate?oauth_token=%s', TwitterOAuth::API_HOST, $response['oauth_token']);
    }

    /**
     * Grants a new access token
     *
     * @return CommonAccessToken
     * @throws ThirdPartyConnectionFailedException
     */
    public function grantNewAccessToken()
    {
        if (empty($_GET['oauth_token'])) {
            throw new ThirdPartyConnectionFailedException('Invalid request!');
        }

        $accessTokenData = $this->twitter->oauth('oauth/access_token', array(
            'oauth_callback' => $this->configuration->redirectUrl(),
            'oauth_token' => $_GET['oauth_token'],
            'oauth_verifier' => $_GET['oauth_verifier'],
        ));

        if (empty($accessTokenData['oauth_token'])) {
            throw new ThirdPartyConnectionFailedException(
                'Failed to establish a new third party vendor connection'
            );
        }

        return new CommonAccessToken(
            sprintf(
                '%s%s%s',
                $accessTokenData['oauth_token'],
                self::TOKEN_SEPARATOR,
                $accessTokenData['oauth_token_secret']
            ),
            ThirdParty::TWITTER
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
        $tokenData = explode(self::TOKEN_SEPARATOR, $accessToken->token());

        $this->twitter = $this->getTwitter($tokenData[0], $tokenData[1]);

        $userInfo = (array) $this->twitter->get('account/verify_credentials', array(
            'oauth_token' => $tokenData[0],
            'oauth_token_secret' => $tokenData[1],
            'include_email' => true,
        ));

        if (empty($userInfo['email'])) {
            throw new NoThirdPartyEmailFoundException('An email address cannot be found from vendor');
        }

        return new ThirdPartyUser(
            $userInfo['id'],
            $userInfo['screen_name'],
            $userInfo['email'],
            !empty($userInfo['profile_image_url']) ? $userInfo['profile_image_url'] : '',
            !empty($userInfo['gender']) ? $userInfo['gender'] : ''
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
        $tokenData = explode(self::TOKEN_SEPARATOR, $accessToken->token());

        $this->twitter = $this->getTwitter($tokenData[0], $tokenData[1]);

        try {
            $this->twitter->post('oauth/invalidate_token', array(
                'access_token' => $tokenData[0],
                'access_token_secret' => $tokenData[1],
            ));
        } catch (TwitterOAuthException $ex) {
            return false;
        }

        return true;
    }

    /**
     * @return TwitterOAuth
     */
    private function getTwitter($oauthToken = null, $oauthTokenSecret = null)
    {
        return new TwitterOAuth(
            $this->configuration->consumerApiKey(),
            $this->configuration->consumerApiSecret(),
            $oauthToken,
            $oauthTokenSecret
        );
    }
}
