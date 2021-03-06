<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 */

namespace TSK\SSO\AppUser;

use TSK\SSO\ThirdParty\ThirdPartyUser;

/**
 * @package TSK\SSO\AppUser
 *
 * Use this to integrate this package with the client application context.
 * This can be used to:
 *     1. Provision new users upon a successful third party sso auth.
 *     2. Sign In incoming users into your system upon a successful third party sso auth.
 */
interface AppUserRepository
{
    /**
     * Use this to provision a new user in the client application side.
     * Upon successful creation, sends an instance of an AppUser
     *
     * @param ThirdPartyUser $thirdPartyUser
     * @return NewAppUser|null
     */
    public function create(ThirdPartyUser $thirdPartyUser);

    /**
     * Returns an application's user representation or null if no user found.
     *
     * @param string $email the email address of the application's user entity
     * @return ExistingAppUser|null
     */
    public function getUser($email);
}
