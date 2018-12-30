<?php
/**
 * @author : Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   : 30-12-2018
 */

namespace TSK\SSO\ThirdParty;

/**
 * @package TSK\SSO\ThirdParty
 *
 * This value object can be used to represent any access token data from any vendor.
 */
class CommonAccessToken
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $vendor;

    /**
     * @var string
     */
    private $expireAt;

    /**
     * @var string
     */
    private $email;

    /**
     * @param string $token
     * @param string $vendor
     * @param string $expireAt
     * @param string|null $email [optional]
     */
    public function __construct($token, $vendor, $expireAt, $email = null)
    {
        $this->token = $token;
        $this->vendor = $vendor;
        $this->expireAt = $expireAt;
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function token()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function vendor()
    {
        return $this->vendor;
    }

    /**
     * @return string
     */
    public function expireAt()
    {
        return $this->expireAt;
    }

    /**
     * @return string
     */
    public function email()
    {
        return $this->email;
    }
}
