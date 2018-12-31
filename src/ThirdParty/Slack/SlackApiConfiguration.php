<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 31-12-2018
 */

namespace TSK\SSO\ThirdParty\Slack;

/**
 * @package TSK\SSO\ThirdParty\Slack
 */
class SlackApiConfiguration
{
    /**
     * @var string
     */
    private $appId;

    /**
     * @var string
     */
    private $appSecret;

    /**
     * @var string
     */
    private $appPermissions;

    /**
     * @var string
     */
    private $redirectUrl;


    /**
     * @param string $appId
     * @param string $appSecret
     * @param string $appPermissions
     * @param string $redirectUrl
     */
    public function __construct($appId, $appSecret, $appPermissions, $redirectUrl)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->appPermissions = $appPermissions;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return string
     */
    public function appId()
    {
        return $this->appId;
    }

    /**
     * @return string
     */
    public function appSecret()
    {
        return $this->appSecret;
    }

    /**
     * @return string
     */
    public function appPermissions()
    {
        return $this->appPermissions;
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
        return 'dfeb6ef625880832f61c6f4bd737e11b';
    }
}
