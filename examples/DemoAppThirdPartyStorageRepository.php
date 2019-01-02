<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 02-01-2018
 */

include_once __DIR__ . '/../vendor/autoload.php';

use TSK\SSO\Storage\FileSystemThirdPartyStorageRepository;
use TSK\SSO\Storage\MappedUser;

class DemoAppThirdPartyStorageRepository extends FileSystemThirdPartyStorageRepository
{
    /**
     * @var string
     */
    private $baseDirectory;

    /**
     * @param string $baseDirectory
     */
    public function __construct($baseDirectory = '/tmp')
    {
        $this->baseDirectory = $baseDirectory;
        parent::__construct($baseDirectory);
    }

    /**
     * @param string $userID
     * @return MappedUser[]
     */
    public function getByUserId($userID)
    {
        $users = $this->getDecodedData();
        if (is_null($users)) {
            return array();
        }

        $connections = array();
        foreach ($users as $vendorAndEmail => $mappedUser) {
            if ($mappedUser['app_user_id'] != $userID) {
                continue;
            }

            $connections[] = new MappedUser(
                $mappedUser['app_user_id'],
                $mappedUser['vendor_name'],
                $mappedUser['vendor_email'],
                $mappedUser['vendor_access_token'],
                $mappedUser['vendor_data']
            );
        }

        return $connections;
    }

    /**
     * @param string $userEmail
     * @param string $vendor
     * @param int $positionId
     */
    public function removeUserMapping($userEmail, $vendor)
    {
        $users = $this->getDecodedData();
        if (is_null($users)) {
            return;
        }

        $key = sprintf('%s::%s', $vendor, $userEmail);
        unset($users[$key]);

        file_put_contents(
            sprintf('%s/%s', $this->baseDirectory, FileSystemThirdPartyStorageRepository::FILE_NAME),
            json_encode($users)
        );
    }
}
