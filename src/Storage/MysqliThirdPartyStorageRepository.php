<?php
/**
 * @author : Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   : 30-12-2018
 */

namespace TSK\SSO\Storage;

use TSK\SSO\AppUser\AppUser;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\ThirdPartyUser;
use mysqli;

/**
 * @package TSK\SSO\Storage
 *
 * This will connect to a MySQL database using the php-mysql driver to persist vendor data.
 */
class MysqliThirdPartyStorageRepository implements ThirdPartyStorageRepository
{
    /**
     * @var mysqli
     */
    private $dbConnection;

    /**
     * @var string
     */
    private $storeName;

    /**
     * @param mysqli $dbConnection
     */
    public function __construct(mysqli $dbConnection, $tableName = 'thirdparty_connections')
    {
        $this->dbConnection = $dbConnection;
        $this->storeName = $tableName;
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
        if (is_null($vendorName)) {
            $sql = "SELECT * FROM `{$this->storeName}` WHERE `vendor_email` = ? LIMIT 1";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("s", $emailAddress);
            $stmt->execute();
        } else {
            $sql = "SELECT * FROM `{$this->storeName}` WHERE `vendor_email` = ? AND `vendor_name` = ? LIMIT 1";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("ss", $emailAddress, $vendorName);
            $stmt->execute();
        }

        $userMap = $stmt->fetch_assoc();
        if (empty($userMap)) {
            return null;
        }

        return new MappedUser(
            $userMap['app_user_id'],
            $userMap['vendor_name'],
            $userMap['vendor_email'],
            $userMap['vendor_access_token'],
            $userMap['vendor_data']
        );
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
        $tokenData = json_encode($accessToken->data());
        $sql = <<<SQL
INSERT INTO `{$this->tableName}`
(
    `app_user_id`,
    `vendor_name`,
    `vendor_email`,
    `vendor_access_token`,
    `vendor_data`,
    `created_at`
)
VALUES
(
    ?, ?, ?, ?, ?, ?, NOW()
)
ON DUPLICATE KEY UPDATE
    `vendor_access_token` = VALUES(`vendor_access_token`),
    `vendor_data` = VALUES(`vendor_data`),
    `updated_at` = NOW()
SQL;

        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param(
            "ssssss",
            $appUser->id(),
            $accessToken->vendor(),
            $accessToken->email(),
            $accessToken->token(),
            $tokenData
        );
        $created = $stmt->execute();
        if (!$created) {
            return false;
        }

        return true;
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
        $sql = "DELETE FROM `{$this->storeName}` WHERE `vendor_email` = ? AND `vendor_name` = ? LIMIT 1";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("ss", $emailAddress, $vendorName);
        $stmt->execute();
    }
}
