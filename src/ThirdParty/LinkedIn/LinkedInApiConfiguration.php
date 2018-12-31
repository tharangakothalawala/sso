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
     * @param string $appId
     * @param string $appSecret
     * @param string $redirectUrl
     */
    public function __construct($appId, $appSecret, $redirectUrl)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
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
    public function redirectUrl()
    {
        return $this->redirectUrl;
    }
}
