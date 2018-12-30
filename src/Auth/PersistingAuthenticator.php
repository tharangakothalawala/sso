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
use TSK\SSO\AppUser\AppUserRepository;
use TSK\SSO\Auth\Exception\AuthenticationFailedException;
use TSK\SSO\Storage\FileSystemThirdPartyStorageRepository;
use TSK\SSO\Storage\ThirdPartyStorageRepository;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\VendorConnection;

/**
 * @package TSK\SSO\Auth
 * @see AppUserAwarePersistingAuthenticator
 *
 * Use this to do a signup/signin via a third party vendor connection by persisting vendor data.
 * It is recomended to use this if you are planning to have more than one sso integration.
 */
class PersistingAuthenticator implements Authenticator
{
    /**
     * @var VendorConnection
     */
    private $thirdPartyConnection;

    /**
     * @var AppUserRepository
     */
    private $appUserRepository;

    /**
     * @var ThirdPartyStorageRepository
     */
    private $storageRepository;

    /**
     * @param VendorConnection $thirdPartyConnection vendor connectin to use to perform an auth.
     * @param AppUserRepository $appUserRepository client application specific user repository implementation to use to provision or validate users.
     * @param ThirdPartyStorageRepository $storageRepository a storage implementation to store the third party auth. will use file system as the default storage.
     */
    public function __construct(
        VendorConnection $thirdPartyConnection,
        AppUserRepository $appUserRepository,
        ThirdPartyStorageRepository $storageRepository = null
    ) {
        $this->thirdPartyConnection = $thirdPartyConnection;
        $this->appUserRepository = $appUserRepository;
        $this->storageRepository = is_null($storageRepository) ? new FileSystemThirdPartyStorageRepository() : $storageRepository;
    }

    /**
     * @throws AuthenticationFailedException
     * @throws NoThirdPartyEmailFoundException
     * @throws ThirdPartyConnectionFailedException
     * @return AppUser
     */
    public function signIn()
    {
        $accessToken = $this->thirdPartyConnection->grantNewAccessToken();

        $thirdPartyUser = $this->thirdPartyConnection->getSelf($accessToken);

        // SIGNIN ATTEMP
        // check if this is a signin attempt with an existing user account
        $existingAppUser = $this->appUserRepository->getUser($thirdPartyUser->email());

        // if no user account found with the same vendor email as the app email, do a mapping lookup in the storage across all vendors 
        if (is_null($existingAppUser)) {
            $mappedUser = $this->storageRepository->getUser($thirdPartyUser->email());
            if (!is_null($mappedUser)) {
                $existingAppUser = new AppUser($mappedUser->appUserId(), $mappedUser->vendorEmail());
            }
        }

        // SIGNUP ATTEMP
        // if no user found previously, let's create a new user as this seems like a signup attempt
        if (is_null($existingAppUser)) {
            $existingAppUser = $this->appUserRepository->create($thirdPartyUser);
            if (!is_null($existingAppUser)) {
                $existingAppUser->markAsNewUser();
            }
        }

        // let's add/update the mapping of the newly created or the existing user's access token before we acknowledge
        if (!is_null($existingAppUser)) {
            $this->storageRepository->save($existingAppUser, $thirdPartyUser, $accessToken);
        }

        // throw error, if still the an app user cannot be resolved.
        if (is_null($existingAppUser)) {
            throw new AuthenticationFailedException('This user cannot be authenticated at this moment');
        }

        return $existingAppUser;
    }
}
