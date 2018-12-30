<?php
/**
 * @author : Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   : 30-12-2018
 */

namespace TSK\SSO\ThirdParty\Google;

/**
 * @package TSK\SSO\ThirdParty\Google
 */
class GoogleApiConfiguration
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
     * @var array
     */
    private $appPermissions;

    /**
     * @var string
     */
    private $redirectUrl;

    /**
     * @param string $appId
     * @param string $appSecret
     * @param array $appPermissions
     * @param string $redirectUrl
     */
    public function __construct($appId, $appSecret, array $appPermissions, $redirectUrl)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->appPermissions = $appPermissions;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return int
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
     * @return array
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
}
