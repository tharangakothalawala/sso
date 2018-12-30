<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 */

namespace TSK\SSO\ThirdParty\Facebook;

/**
 * @package TSK\SSO\ThirdParty\Facebook
 */
class FacebookApiConfiguration
{
    /**
     * @var string
     */
    private $apiVersion;

    /**
     * @var int
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
     * @param string $apiVersion
     * @param int $appId
     * @param string $appSecret
     * @param string $appPermissions
     * @param string $redirectUrl
     */
    public function __construct($apiVersion, $appId, $appSecret, $appPermissions, $redirectUrl)
    {
        $this->apiVersion = $apiVersion;
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->appPermissions = $appPermissions;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return string
     */
    public function apiVersion()
    {
        return $this->apiVersion;
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
}
