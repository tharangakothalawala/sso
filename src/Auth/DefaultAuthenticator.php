<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 *
 * This will do a lookup in the users store in the client application
 */

namespace TSK\SSO\Auth;

use TSK\SSO\AppUser\AppUser;
use TSK\SSO\AppUser\AppUserRepository;
use TSK\SSO\Auth\Exception\AuthenticationFailedException;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\VendorConnection;

/**
 * @package TSK\SSO\Auth
 * @see PersistingAuthenticator
 *
 * Use this to do a signup/signin via a third party vendor connection.
 * It is recomended to use this if are planning to have only one sso integration.
 */
class DefaultAuthenticator implements Authenticator
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
     * @param VendorConnection $thirdPartyConnection vendor connectin to use to perform an auth.
     * @param AppUserRepository $appUserRepository client application specific user repository implementation to use to provision or validate users.
     */
    public function __construct(
        VendorConnection $vendorConnection,
        AppUserRepository $appUserRepository
    ) {
        $this->vendorConnection = $vendorConnection;
        $this->appUserRepository = $appUserRepository;
    }

    /**
     * @throws AuthenticationFailedException
     * @throws NoThirdPartyEmailFoundException
     * @throws ThirdPartyConnectionFailedException
     * @return AppUser
     */
    public function signIn()
    {
        $accessToken = $this->vendorConnection->grantNewAccessToken();

        $thirdPartyUser = $this->vendorConnection->getSelf($accessToken);

        // SIGNIN ATTEMP
        // check if this is a signin attempt with an existing user account
        $existingAppUser = $this->appUserRepository->getUser($thirdPartyUser->email());

        // SIGNUP ATTEMP
        // if no user found previously, let's create a new user as this seems like a signup attempt
        if (is_null($existingAppUser)) {
            $existingAppUser = $this->appUserRepository->create($thirdPartyUser);
            if (!is_null($existingAppUser)) {
                $existingAppUser->markAsNewUser();
            }
        }

        // if still the an app user cannot be resolved, throw error.
        if (is_null($existingAppUser)) {
            throw new AuthenticationFailedException('This user cannot be authenticated at this moment');
        }

        return $existingAppUser;
    }
}
