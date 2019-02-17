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

class DemoAppUserRepository implements AppUserRepository
{
    const FILE_NAME = 'tsk.sso.demo-user-store.json';

    /**
     * @var string
     */
    private $userDataBase;

    public function __construct($baseDir)
    {
        $this->userDataBase = sprintf('%s/%s', $baseDir, self::FILE_NAME);
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
        $userId = rand(100, 999);

        $data = $this->getDecodedData();
        $data[$userId] = array(
            'id' => $userId,
            'name' => $thirdPartyUser->name(),
            'email' => $thirdPartyUser->email(),
            'picture' => $thirdPartyUser->avatar(),
            'gender' => $thirdPartyUser->gender(),
        );

        file_put_contents($this->userDataBase, json_encode($data));

        return new NewAppUser($userId, $thirdPartyUser->email());
    }

    /**
     * Returns an application's user representation or null if no user found.
     *
     * @param string $email the email address of the application's user entity
     * @return ExistingAppUser|null
     */
    public function getUser($email)
    {
        $users = $this->getDecodedData();

        foreach ($users as $userId => $user) {
            if ($user['email'] !== $email) {
                continue;
            }

            return new ExistingAppUser($user['id'], $user['email']);
        }

        return null;
    }

    /**
     * Returns a user with full details
     *
     * @param string $email the email address of the application's user entity
     * @return array|null
     */
    public function getUserAsArray($id)
    {
        $users = $this->getDecodedData();

        foreach ($users as $userId => $user) {
            if ($userId !== $id) {
                continue;
            }

            return $user;
        }

        return null;
    }

    /**
     * @return array
     */
    private function getDecodedData()
    {
        return json_decode(file_get_contents($this->userDataBase), true);
    }
}
