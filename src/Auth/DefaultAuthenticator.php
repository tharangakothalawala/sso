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
 * It is recommended to use this if are planning to have only one sso integration.
 */
class DefaultAuthenticator implements Authenticator
{
    /**
     * @var AppUserRepository
     */
    private $appUserRepository;

    /**
     * @param AppUserRepository $appUserRepository client application specific user repository implementation to use
     *        to provision or validate users.
     */
    public function __construct(AppUserRepository $appUserRepository)
    {
        $this->appUserRepository = $appUserRepository;
    }

    /**
     * This will try to authenticate a user using any given vendor connection.
     * Upon a successful attempt, returns the authenticated user.
     *
     * @param VendorConnection $thirdPartyConnection vendor connection to use to perform an auth
     * @return AppUser
     *
     * @throws AuthenticationFailedException
     * @throws NoThirdPartyEmailFoundException
     * @throws ThirdPartyConnectionFailedException
     */
    public function authenticate(VendorConnection $thirdPartyConnection)
    {
        $accessToken = $thirdPartyConnection->grantNewAccessToken();

        $thirdPartyUser = $thirdPartyConnection->getSelf($accessToken);

        // a SIGN-IN attempt
        // check if this is a signin attempt with an existing user account
        $existingAppUser = $this->appUserRepository->getUser($thirdPartyUser->email());

        // a SIGN-UP attempt
        // if no user found previously, let's create a new user as this seems like a signup attempt
        if (is_null($existingAppUser)) {
            $existingAppUser = $this->appUserRepository->create($thirdPartyUser);
        }

        // if still the an app user cannot be resolved, throw error.
        if (is_null($existingAppUser)) {
            throw new AuthenticationFailedException('This user cannot be authenticated at this moment');
        }

        return $existingAppUser;
    }
}
