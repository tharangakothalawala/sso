<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 31-12-2018
 */

namespace TSK\SSO\ThirdParty\LinkedIn;

/**
 * @package TSK\SSO\ThirdParty\LinkedIn
 */
class LinkedInApiConfiguration
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
    private $redirectUrl;

    /**
     * @var string
     */
    private $permissions;

    /**
     * @param string $appId
     * @param string $appSecret
     * @param string $redirectUrl
     * @param string $permissions
     */
    public function __construct($appId, $appSecret, $redirectUrl, $permissions)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->redirectUrl = $redirectUrl;
        $this->permissions = $permissions;
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
    public function redirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * @return string
     */
    public function permissions()
    {
        return $this->permissions;
    }

    /**
     * This is just to identify that, we initiated the login sequence (not someone else) and to prevent CSRF.
     *
     * @return string
     */
    public function ourSecretState()
    {
        return 'dfeb6ef625880832f61c6f4bd737e11b';
    }
}
