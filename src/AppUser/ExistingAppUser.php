<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 */

namespace TSK\SSO\AppUser;

/**
 * @package TSK\SSO\AppUser
 *
 * This represents an existing user within the client application with their basic data.
 */
class ExistingAppUser extends AppUser
{
    /**
     * Returns true if this user is an existing user or newly created one.
     *
     * @return bool
     */
    public function isExistingUser()
    {
        return true;
    }
}
