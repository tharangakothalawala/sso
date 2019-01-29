<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 *
 * This is similar to the PersistingAuthenticator version but is user aware.
 * Meaning this can be used to link third party connections while the user is logged in. (user aware).
 * use this if you want to connect & use multiple vendor logins such as Facebook and/or Google while logged in.
 */

namespace TSK\SSO\Auth;

use TSK\SSO\AppUser\AppUser;
use TSK\SSO\AppUser\ExistingAppUser;
use TSK\SSO\Storage\Exception\DataCannotBeStoredException;
use TSK\SSO\Storage\FileSystemThirdPartyStorageRepository;
use TSK\SSO\Storage\ThirdPartyStorageRepository;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\VendorConnection;

/**
 * @package TSK\SSO\Auth
 * @see PersistingAuthenticator
 *
 * Use this to validate and connect other accounts belong to the same vendor.
 * You need to use PersistingAuthenticator when the user is NOT logged in yet.
 *
 * ex: A user can have three(3) Google Mail accounts.
 *
 * You may want to let users connect the rest of their account while they are LOGGED IN (aka user aware).
 */
class AppUserAwarePersistingAuthenticator implements Authenticator
{
    /**
     * @var AppUser
     */
    private $appUser;

    /**
     * @var ThirdPartyStorageRepository
     */
    private $storageRepository;

    /**
     * @param AppUser $appUser client application user which can be used to connect multiple other vendor accounts.
     * @param ThirdPartyStorageRepository $storageRepository a storage implementation to store the third party auth
     *        data. By default uses file system as the storage.
     */
    public function __construct(AppUser $appUser, ThirdPartyStorageRepository $storageRepository = null)
    {
        $this->appUser = $appUser;
        $this->storageRepository = is_null($storageRepository)
            ? new FileSystemThirdPartyStorageRepository()
            : $storageRepository;
    }

    /**
     * This will try to authenticate a user using any given vendor connection.
     * Upon a successful attempt, returns the authenticated user.
     *
     * @param VendorConnection $thirdPartyConnection vendor connection to use to perform an auth
     * @return AppUser
     *
     * @throws DataCannotBeStoredException
     * @throws NoThirdPartyEmailFoundException
     * @throws ThirdPartyConnectionFailedException
     */
    public function authenticate(VendorConnection $thirdPartyConnection)
    {
        $accessToken = $thirdPartyConnection->grantNewAccessToken();

        $thirdPartyUser = $thirdPartyConnection->getSelf($accessToken);

        /**
         * Resolving a new app user by taking
         *  - the known user's application id
         *  - and the incoming new vendor account's email
         */
        $derivedAppUser = new ExistingAppUser($this->appUser->id(), $thirdPartyUser->email());

        // Let's store this new third party user mapping against the existing user
        $this->storageRepository->save($derivedAppUser, $thirdPartyUser, $accessToken);

        return $derivedAppUser;
    }
}
