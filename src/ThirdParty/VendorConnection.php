<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 */

namespace TSK\SSO\ThirdParty;

use TSK\SSO\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\Exception\ThirdPartyConnectionFailedException;

/**
 * @package TSK\SSO\ThirdParty
 *
 * Any third party vendor connection must implement these basic functionality to smoothly integrate and operate vendor based SSO.
 */
interface VendorConnection
{
    /**
     * Use this to get a link to redirect a user to the third party login
     *
     * @return string
     */
    public function getGrantUrl();

    /**
     * Grants a new access token
     *
     * @return CommonAccessToken
     * @throws ThirdPartyConnectionFailedException
     */
    public function grantNewAccessToken();

    /**
     * Use this to retrieve the current user's third party user data using there existing granted access token
     *
     * @param CommonAccessToken $accessToken
     * @return ThirdPartyUser
     * @throws NoThirdPartyEmailFoundException
     * @throws ThirdPartyConnectionFailedException
     */
    public function getSelf(CommonAccessToken $accessToken);

    /**
     * Use this to revoke the access to the third party data. this will completely remove the access from the vendor side.
     *
     * @param CommonAccessToken $accessToken
     * @return bool
     */
    public function revokeAccess(CommonAccessToken $accessToken);
}
