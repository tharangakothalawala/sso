<?php
/**
 * @author : Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   : 30-12-2018
 */

namespace TSK\SSO\AppUser;

/**
 * @package TSK\SSO\AppUser
 *
 * This represents a user within the client application with their basic data.
 */
class AppUser
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var bool
     */
    private $isExistingUser = true;

    /**
     * @param string $id the unique id of the application's user entity. ex: UUID
     * @param string $email the email address of the application's user entity
     */
    public function __construct($id, $email)
    {
        $this->id = $id;
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function email()
    {
        return $this->email;
    }

    /**
     * @return bool
     */
    public function isExistingUser()
    {
        return $this->isExistingUser;
    }

    public function markAsNewUser()
    {
        $this->isExistingUser = false;
    }
}
