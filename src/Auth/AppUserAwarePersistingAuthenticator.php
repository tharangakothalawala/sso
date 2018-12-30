<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 *
 * This is similar to the Default version. But this also stores the vendor token user details in a given storage by mapping the client application user record.
 * use this if you want to connect & use multiple vendor login such as Facebook and/or Google.
 */

namespace TSK\SSO\Auth;

use TSK\SSO\AppUser\AppUser;
use TSK\SSO\Auth\Exception\AuthenticationFailedException;
use TSK\SSO\Storage\FileSystemThirdPartyStorageRepository;
use TSK\SSO\Storage\ThirdPartyStorageRepository;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\VendorConnection;

/**
 * @package TSK\SSO\Auth
 * @see PersistingAuthenticator
 *
 * Use this to validate and connect other accounts belong to the same vendor. You need to use PersistingAuthenticator for logins.
 *
 * ex: A user can have three(3) Google Mail accounts.
 *
 * You may want to let users connect the rest of their account while they are LOGGED IN (aka user aware).
 */
class AppUserAwarePersistingAuthenticator implements Authenticator
{
    /**
     * @var VendorConnection
     */
    private $thirdPartyConnection;

    /**
     * @var AppUser
     */
    private $appUser;

    /**
     * @var ThirdPartyStorageRepository
     */
    private $storageRepository;

    /**
     * @param VendorConnection $thirdPartyConnection vendor connectin to use to perform an auth.
     * @param AppUser $appUser client application user which can be used to connect multiple other vendor accounts.
     * @param ThirdPartyStorageRepository $storageRepository a storage implementation to store the third party auth. will use file system as the default storage.
     */
    public function __construct(
        VendorConnection $thirdPartyConnection,
        AppUser $appUser,
        ThirdPartyStorageRepository $storageRepository = null
    ) {
        $this->thirdPartyConnection = $thirdPartyConnection;
        $this->appUser = $appUser;
        $this->storageRepository = is_null($storageRepository) ? new FileSystemThirdPartyStorageRepository() : $storageRepository;
    }

    /**
     * @throws NoThirdPartyEmailFoundException
     * @throws ThirdPartyConnectionFailedException
     * @return AppUser
     */
    public function signIn()
    {
        $accessToken = $this->thirdPartyConnection->grantNewAccessToken();

        $thirdPartyUser = $this->thirdPartyConnection->getSelf($accessToken);

        /*
          Resolving a new app user by taking
            - the known user's application id
            - and the incoming new vendor account's email.
         */
        $derivedAppUser = new AppUser($this->appUser->id(), $thirdPartyUser->email());

        // Let's add this new third party user mapping for the existing user into storage
        $this->storageRepository->save($derivedAppUser, $thirdPartyUser, $accessToken);

        return $derivedAppUser;
    }
}
