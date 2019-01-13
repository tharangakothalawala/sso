<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 13-01-2019
 */

namespace TSK\SSO\ThirdParty\Twitter;

/**
 * @package TSK\SSO\ThirdParty\Twitter
 */
class TwitterApiConfiguration
{
    /**
     * @var string
     */
    private $consumerApiKey;

    /**
     * @var string
     */
    private $consumerApiSecret;

    /**
     * @var string
     */
    private $redirectUrl;

    /**
     * TwitterApiConfiguration constructor.
     * @param string $consumerApiKey
     * @param string $consumerApiSecret
     * @param string $redirectUrl
     */
    public function __construct($consumerApiKey, $consumerApiSecret, $redirectUrl)
    {
        $this->consumerApiKey = $consumerApiKey;
        $this->consumerApiSecret = $consumerApiSecret;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return string
     */
    public function consumerApiKey()
    {
        return $this->consumerApiKey;
    }

    /**
     * @return string
     */
    public function consumerApiSecret()
    {
        return $this->consumerApiSecret;
    }

    /**
     * @return string
     */
    public function redirectUrl()
    {
        return $this->redirectUrl;
    }
}
