<?php
/**
 * @author : Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   : 30-12-2018
 */

namespace TSK\SSO\ThirdParty;

/**
 * @package TSK\SSO\ThirdParty
 *
 * This value object represents a third party user with their basic user data.
 */
class ThirdPartyUser
{
    const ID = 'id';

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $avatar = '';

    /**
     * @var string
     */
    private $gender = '';

    /**
     * @param string $id
     * @param string $name
     * @param string $email
     * @param string $avatar [optional]
     * @param string $gender [optional]
     */
    public function __construct($id, $name, $email, $avatar = null, $gender = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->avatar = $avatar;
        $this->gender = $gender;
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
    public function name()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function email()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function avatar()
    {
        return $this->avatar;
    }

    /**
     * @return string
     */
    public function gender()
    {
        return $this->gender;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            self::ID => $this->id(),
            'name' => $this->name(),
            'email' => $this->email(),
            'avatar' => $this->avatar(),
            'gender' => $this->gender(),
        );
    }
}
