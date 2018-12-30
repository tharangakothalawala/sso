<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 */

namespace TSK\SSO\Storage;

use TSK\SSO\AppUser\AppUser;
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

            $seachKey = sprintf('%s::%s', $vendorName, $emailAddress);
            if ($vendorAndEmail !== $seachKey) {
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
    }

    /**
     * @param AppUser $appUser
     * @param ThirdPartyUser $thirdPartyUser
     * @param CommonAccessToken $accessToken
     * @return bool
     */
    public function save(
        AppUser $appUser,
        ThirdPartyUser $thirdPartyUser,
        CommonAccessToken $accessToken
    ) {
        $data = $this->getDecodedData();
        if (is_null($data)) {
            return false;
        }

        $key = sprintf('%s::%s', $accessToken->vendor(), $accessToken->email());
        $data[$key] = array(
            'app_user_id' => $appUser->id(),
            'vendor_name' => $accessToken->vendor(),
            'vendor_email' => $accessToken->email(),
            'vendor_access_token' => $accessToken->token(),
            'vendor_data' => $thirdPartyUser->toArray(),
            'created_at' => date('Y-m-d H:i:00'),
        );

        return file_put_contents($this->fileAbsolutePath(), json_encode($data));
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

        $key = sprintf('%s::%s', $accessToken->vendor(), $accessToken->email());
        unset($data[$key]);
        return file_put_contents($this->fileAbsolutePath(), json_encode($data));
    }

    /**
     * @return array|null
     */
    private function getDecodedData()
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

    private function createFile()
    {
        if (file_exists($this->fileAbsolutePath())) {
            return;
        }

        file_put_contents($this->fileAbsolutePath(), '{}');
    }

    /**
     * @return string
     */
    private function fileAbsolutePath()
    {
        return sprintf('%s/%s', $this->baseDirectory, self::FILE_NAME);
    }
}
