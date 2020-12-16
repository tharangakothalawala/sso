<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 */

namespace TSK\SSO\ThirdParty;

/**
 * @package TSK\SSO\ThirdParty
 */
class CommonAccessToken
{
    /**
     * @var string the access token string
     */
    private $token;

    /**
     * @var string name of the vendor. ex: Google
     */
    private $vendor;

    /**
     * @var string email used within the vendor platform
     */
    private $email;

    /**
     * @var string token used to refresh this access token
     */
    private $refreshToken;

    /**
     * This value object can be used to represent an access token by any third party vendor.
     *
     * @param string $token the access token string
     * @param string $vendor name of the vendor. ex: Google
     * @param string|null $email [optional] associated email address to this token used within the vendor platform
     */
    public function __construct($token, $vendor, $email = null)
    {
        $this->token = $token;
        $this->vendor = $vendor;
        $this->email = $email;
    }

    /**
     * returns the access token string
     *
     * @return string
     */
    public function token()
    {
        return $this->token;
    }

    /**
     * returns name of the vendor. ex: Google
     *
     * @return string
     */
    public function vendor()
    {
        return $this->vendor;
    }

    /**
     * returns the associated email address to this token used within the vendor platform if any
     *
     * @return string
     */
    public function email()
    {
        return $this->email;
    }

    /**
     * returns the token used to refresh this access token
     *
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Set the token used to refresh this access token
     *
     * @param string $refreshToken refresh token
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }
}
