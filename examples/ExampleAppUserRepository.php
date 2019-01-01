<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 31-12-2018
 */

include_once __DIR__ . '/../vendor/autoload.php';

use TSK\SSO\AppUser\AppUser;
use TSK\SSO\AppUser\AppUserRepository;
use TSK\SSO\AppUser\ExistingAppUser;
use TSK\SSO\AppUser\NewAppUser;
use TSK\SSO\ThirdParty\ThirdPartyUser;

class ExampleAppUserRepository implements AppUserRepository
{
    private $userDataBase = './tsk.sso.demo-user-store.json';

    public function __construct()
    {
        if (!file_exists($this->userDataBase)) {
            file_put_contents($this->userDataBase, '{}');
        }
    }

    /**
     * Use this to provision a new user in the client application side.
     * Upon successful creation, sends a an instance of an AppUser
     *
     * @param ThirdPartyUser $thirdPartyUser
     * @return AppUser|null
     */
    public function create(ThirdPartyUser $thirdPartyUser)
    {
        $string = json_encode($thirdPartyUser->toArray());
        file_put_contents($this->userDataBase, $string);

        return new NewAppUser($thirdPartyUser->id(), $thirdPartyUser->email());
    }

    /**
     * Returns an application's user representation or null if no user if found.
     *
     * @param string $email the email address of the application's user entity
     * @return ExistingAppUser|null
     */
    public function getUser($email)
    {
        return new ExistingAppUser('4ebdaa95-74b8-4eef-86b1-def7dc06aed1', $email);
    }
}
