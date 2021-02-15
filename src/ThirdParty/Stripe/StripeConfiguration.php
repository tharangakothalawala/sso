<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   15-02-2021
 */

namespace TSK\SSO\ThirdParty\Stripe;

/**
 * This represents a stripe oauth configuration
 *
 * @package TSK\SSO\ThirdParty\Stripe
 */
class StripeConfiguration
{
    /**
     * @var string Stripe Client ID
     * @see https://dashboard.stripe.com/settings/applications
     */
    private $clientId;

    /**
     * @var string Stripe Client Secret
     */
    private $clientSecret;

    /**
     * @var string Redirection URL back to the client application
     */
    private $redirectUrl;

    /**
     * StripeConfiguration constructor.
     *
     * @param string $clientId     Stripe Client ID
     * @param string $clientSecret Stripe Client Secret
     * @param string $redirectUrl  Redirection URL back to the client application
     */
    public function __construct($clientId, $clientSecret, $redirectUrl)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return string
     */
    public function clientId()
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function clientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @return string
     */
    public function redirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * This is just to identify that, we initiated the login sequence (not someone else)
     *
     * @return string
     */
    public function ourSecretState()
    {
        return md5($this->clientId);
    }
}
