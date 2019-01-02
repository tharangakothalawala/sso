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
 *
 * This will use the server file system to persist vendor data.
 */
class FileSystemThirdPartyStorageRepository implements ThirdPartyStorageRepository
{
    const FILE_NAME = 'tsk.sso.storage.json';

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
        $this->createFile();
    }

    /**
     * Returns a mapped full user record for a given vendor email address or null.
     *
     * @param string $emailAddress email address used within the given vendor system
     * @param string|null $vendorName [optional] name of the third party vendor for vendor filtering
     * @return MappedUser|null
     */
    public function getUser($emailAddress, $vendorName = null)
    {
        $fileDataDecoded = $this->getDecodedData();
        if (is_null($fileDataDecoded)) {
            return null;
        }

        foreach ($fileDataDecoded as $vendorAndEmail => $mappedUser) {
            if (is_null($vendorName)) {
                $vendorName = $mappedUser['vendor_name'];
            }

            $searchKey = sprintf('%s::%s', $vendorName, $emailAddress);
            if ($vendorAndEmail !== $searchKey) {
                continue;
            }

            return new MappedUser(
                $mappedUser['app_user_id'],
                $mappedUser['vendor_name'],
                $mappedUser['vendor_email'],
                $mappedUser['vendor_access_token'],
                $mappedUser['vendor_data']
            );
        }

        return null;
    }

    /**
     * @param AppUser $appUser
     * @param ThirdPartyUser $thirdPartyUser
     * @param CommonAccessToken $accessToken
     * @throws DataCannotBeStoredException
     */
    public function save(
        AppUser $appUser,
        ThirdPartyUser $thirdPartyUser,
        CommonAccessToken $accessToken
    ) {
        $data = $this->getDecodedData();
        if (is_null($data)) {
            return;
        }

        $key = sprintf('%s::%s', $accessToken->vendor(), $thirdPartyUser->email());
        $data[$key] = array(
            'app_user_id' => $appUser->id(),
            'vendor_name' => $accessToken->vendor(),
            'vendor_email' => $thirdPartyUser->email(),
            'vendor_access_token' => $accessToken->token(),
            'vendor_data' => json_encode($thirdPartyUser->toArray()),
            'created_at' => date('Y-m-d H:i:00'),
        );

        $written = file_put_contents($this->fileAbsolutePath(), json_encode($data));
        if (!$written) {
            throw new DataCannotBeStoredException("Couldn't save the third party user data due to a file system error");
        }
    }

    /**
     * Use this to clean the storage after revoking access to a third party for example.
     *
     * @param string $emailAddress email address used within the given vendor system
     * @param string $vendorName name of the third party where the given email belongs to
     * @return bool
     */
    public function remove($emailAddress, $vendorName)
    {
        $data = $this->getDecodedData();
        if (is_null($data)) {
            return false;
        }

        $key = sprintf('%s::%s', $vendorName, $emailAddress);
        unset($data[$key]);
        return file_put_contents($this->fileAbsolutePath(), json_encode($data));
    }

    /**
     * Returns an array containing raw third party user mappings.
     *
     * @return array|null
     */
    protected function getDecodedData()
    {
        $fileData = file_get_contents($this->fileAbsolutePath());
        if (empty($fileData)) {
            return null;
        }

        $fileDataDecoded = @json_decode($fileData, true);
        if ($fileDataDecoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        if (!is_array($fileDataDecoded)) {
            return null;
        }

        return $fileDataDecoded;
    }

    /**
     * Creates a new storage file in the file system. empty json object will be added if the file is empty.
     */
    private function createFile()
    {
        if (!file_exists($this->fileAbsolutePath())) {
            file_put_contents($this->fileAbsolutePath(), '{}');
        }

        $contents = file_get_contents($this->fileAbsolutePath());
        if (empty($contents)) {
            file_put_contents($this->fileAbsolutePath(), '{}');
        }
    }

    /**
     * @return string
     */
    private function fileAbsolutePath()
    {
        return sprintf('%s/%s', $this->baseDirectory, self::FILE_NAME);
    }
}
