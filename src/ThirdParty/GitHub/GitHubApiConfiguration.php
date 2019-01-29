<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 29-01-2019
 */

namespace TSK\SSO\ThirdParty\GitHub;

/**
 * @package TSK\SSO\ThirdParty\GitHub
 */
class GitHubApiConfiguration
{
    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $redirectUrl;

    /**
     * @var string
     */
    private $oauthAppName;

    /**
     * GitHubApiConfiguration constructor.
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUrl
     * @param string $oauthAppName
     */
    public function __construct($clientId, $clientSecret, $redirectUrl, $oauthAppName)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
        $this->oauthAppName = $oauthAppName;
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
     * @return string
     */
    public function oauthAppName()
    {
        return $this->oauthAppName;
    }

    /**
     * This is just to identify that, we initiated the login sequence (not someone else)
     *
     * @return string
     */
    public function ourSecretState()
    {
        return 'dfeb6ef625880832f61c6f4bd737e11b';
    }
}
