<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 */

namespace TSK\SSO\Auth;

use TSK\SSO\AppUser\AppUser;
use TSK\SSO\Auth\Exception\AuthenticationFailedException;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\VendorConnection;

/**
 * @package TSK\SSO\Auth
 */
interface Authenticator
{
    /**
     * This will try to authenticate a user using any given vendor connection.
     * Upon a successful attempt, returns the authenticated user.
     *
     * @param VendorConnection $thirdPartyConnection vendor connection to use to perform an auth
     * @return AppUser
     *
     * @throws AuthenticationFailedException
     * @throws NoThirdPartyEmailFoundException
     * @throws ThirdPartyConnectionFailedException
     */
    public function authenticate(VendorConnection $thirdPartyConnection);
}
