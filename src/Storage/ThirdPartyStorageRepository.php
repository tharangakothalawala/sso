<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 */

namespace TSK\SSO\Storage;

use TSK\SSO\AppUser\AppUser;
use TSK\SSO\Storage\Exception\DataCannotBeStoredException;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\ThirdPartyUser;

/**
 * @package TSK\SSO\Storage
 * @see PersistingAuthenticator
 * @see FileSystemThirdPartyStorageRepository
 *
 * This is used to persist the vendor connection data in your own storage mechanics.
 * ex: MySQL, Mongo, Cloud or even in File System
 *
 * Implement this to use the PersistingAuthenticator for example.
 */
interface ThirdPartyStorageRepository
{
    /**
     * Returns a mapped full user record for a given vendor email address or null.
     *
     * @param string $emailAddress email address used within the given vendor system
     * @param string|null $vendorName [optional] name of the third party vendor for vendor filtering
     * @return MappedUser|null
     */
    public function getUser($emailAddress, $vendorName = null);

    /**
     * Returns any vendor MappedUser list for a given Application user.
     *
     * @param AppUser $appUser
     * @return MappedUser[]
     */
    public function getByAppUser(AppUser $appUser);

    /**
     * This will insert or update a vendor connection which contains the user mapping.
     *
     * @param AppUser $appUser
     * @param ThirdPartyUser $thirdPartyUser
     * @param CommonAccessToken $accessToken
     * @throws DataCannotBeStoredException
     */
    public function save(
        AppUser $appUser,
        ThirdPartyUser $thirdPartyUser,
        CommonAccessToken $accessToken
    );

    /**
     * Use this to clean the storage after revoking access to a third party for example.
     *
     * @param string $emailAddress email address used within the given vendor system
     * @param string $vendorName name of the third party where the given email belongs to
     * @return bool
     */
    public function remove($emailAddress, $vendorName);
}
