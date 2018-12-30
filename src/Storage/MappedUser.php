<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 */

namespace TSK\SSO\Storage;

/**
 * @internal
 * @package TSK\SSO\Storage
 * @see TSK\SSO\AppUser\AppUser
 * @see TSK\SSO\ThirdParty\ThirdPartyUser
 *
 * This represent a mapping between the third party user(ThirdPartyUser) with the client application user(AppUser).
 */
class MappedUser
{
    /**
     * @var string
     */
    private $appUserId;

    /**
     * @var string
     */
    private $vendorName;

    /**
     * @var string
     */
    private $vendorEmail;

    /**
     * @var string
     */
    private $vendorToken;

    /**
     * @var string
     */
    private $vendorData;

    /**
     * @param string $appUserId application's use id. ex: can be a UUID or an integer
     * @param string $vendorName name of the vendor. ex: Google, Facebook, LinkedIn
     * @param string $vendorEmail user's email address at third party vendor's end
     * @param string $vendorToken
     * @param string $vendorData JSON encoded vendor user extra data.
     */
    public function __construct($appUserId, $vendorName, $vendorEmail, $vendorToken, $vendorData)
    {
        $this->appUserId = $appUserId;
        $this->vendorName = $vendorName;
        $this->vendorEmail = $vendorEmail;
        $this->vendorToken = $vendorToken;
        $this->vendorData = $vendorData;
    }

    /**
     * @return string
     */
    public function appUserId()
    {
        return $this->appUserId;
    }

    /**
     * @return string
     */
    public function vendorName()
    {
        return $this->vendorName;
    }

    /**
     * @return string
     */
    public function vendorEmail()
    {
        return $this->vendorEmail;
    }

    /**
     * @return string
     */
    public function vendorToken()
    {
        return $this->vendorToken;
    }

    /**
     * @return string
     */
    public function vendorData()
    {
        return $this->vendorData;
    }

    /**
     * @return array
     */
    public function decodedVendorData()
    {
        $decoded = @json_decode($this->vendorData(), true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return array();
        }

        return $decoded;
    }
}
