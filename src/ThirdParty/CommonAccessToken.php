<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 */

namespace TSK\SSO\ThirdParty;

/**
 * @package TSK\SSO\ThirdParty
 *
 * This value object can be used to represent an access token by any third party vendor.
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
    private $email;

    /**
     * @param string $token
     * @param string $vendor
     * @param string|null $email [optional] associated email address to this token
     */
    public function __construct($token, $vendor, $email = null)
    {
        $this->token = $token;
        $this->vendor = $vendor;
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
    public function email()
    {
        return $this->email;
    }
}
