<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 17-02-2019
 */

namespace TSK\SSO\ThirdParty;

use TSK\SSO\Storage\FileSystemThirdPartyStorageRepository;
use TSK\SSO\Storage\ThirdPartyStorageRepository;

/**
 * @package TSK\SSO\ThirdParty
 *
 * This class is capable of revoking vendor connection access
 */
class VendorConnectionRevoker
{
    /**
     * @var VendorConnection
     */
    private $thirdPartyConnection;

    /**
     * @var ThirdPartyStorageRepository
     */
    private $storageRepository;

    /**
     * @param VendorConnection $thirdPartyConnection vendor connection to use for the revoke action
     * @param ThirdPartyStorageRepository $storageRepository [optional] the storage implementation
     *        which was used to store the data for the first time. By default uses file system as the storage.
     */
    public function __construct(
        VendorConnection $thirdPartyConnection,
        ThirdPartyStorageRepository $storageRepository = null
    ) {
        $this->thirdPartyConnection = $thirdPartyConnection;
        $this->storageRepository = is_null($storageRepository)
            ? new FileSystemThirdPartyStorageRepository()
            : $storageRepository;
    }

    /**
     * Use this to revoke the app's access to the third party and to remove the local vendor user mappings.
     *
     * @return bool
     */
    public function revoke($vendorEmail, $vendorName)
    {
        // return false if no vendor user mapping found
        $mappedUser = $this->storageRepository->getUser($vendorEmail, $vendorName);
        if (is_null($mappedUser)) {
            return false;
        }

        $isRevoked = $this->thirdPartyConnection->revokeAccess(
            new CommonAccessToken($mappedUser->vendorToken(), $mappedUser->vendorName(), $mappedUser->vendorEmail())
        );

        if (!$isRevoked) {
            return false;
        }

        return $this->storageRepository->remove($vendorEmail, $vendorName);
    }
}
