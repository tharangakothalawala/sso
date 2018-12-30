<?php
/**
 * @author : Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   : 30-12-2018
 */

namespace TSK\SSO\Storage;

use TSK\SSO\AppUser\AppUser;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\ThirdPartyUser;
use PDOException;
use PDO;

/**
 * @package TSK\SSO\Storage
 *
 * This will connect to a MySQL database using the php-pdo driver to persist vendor data.
 */
class PdoThirdPartyStorageRepository implements ThirdPartyStorageRepository
{
    /**
     * @var PDO
     */
    private $dbConnection;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @param PDO $dbConnection
     */
    public function __construct(PDO $dbConnection, $tableName = 'thirdparty_connections')
    {
        $this->dbConnection = $dbConnection;
        $this->tableName = $tableName;
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
            $sql = "SELECT * FROM `{$this->tableName}` WHERE `vendor_email` = :email LIMIT 1";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindParam(':email', $emailAddress, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $sql = "SELECT * FROM `{$this->tableName}` WHERE `vendor_email` = :email AND `vendor_name` = :vendor LIMIT 1";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindParam(':email', $emailAddress, PDO::PARAM_STR);
            $stmt->bindParam(':vendor', $vendorName, PDO::PARAM_STR);
            $stmt->execute();
        }

        $userMap = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($userMap)) {
            return null;
        }

        return new MappedUser(
            $userMap['app_user_id'],
            $userMap['vendor_name'],
            $userMap['vendor_email'],
            $userMap['vendor_access_token'],
            $userMap['vendor_data'],
            $userMap['vendor_expire_at']
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
        $sql = <<<SQL
INSERT INTO `{$this->tableName}`
(
    `app_user_id`,
    `vendor_name`,
    `vendor_email`,
    `vendor_access_token`,
    `vendor_data`,
    `vendor_expire_at`,
    `created_at`
)
VALUES
(
    :appUserId,
    :vendorName,
    :vendorEmail,
    :vendorToken,
    :vendorData,
    :expireAt,
    NOW()
)
ON DUPLICATE KEY UPDATE
    `vendor_access_token` = VALUES(`vendor_access_token`),
    `vendor_data` = VALUES(`vendor_data`),
    `vendor_expire_at` = VALUES(`vendor_expire_at`),
    `updated_at` = NOW()
SQL;
        try {
            $vendorData = json_encode($thirdPartyUser->toArray());
            $appUserId = $appUser->id();
            $vendorName = $accessToken->vendor();
            $vendorEmail = $accessToken->email();
            $vendorToken = $accessToken->token();
            $expireAt = $accessToken->expireAt();

            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindParam(':appUserId', $appUserId, PDO::PARAM_STR);
            $stmt->bindParam(':vendorName', $vendorName, PDO::PARAM_STR);
            $stmt->bindParam(':vendorEmail', $vendorEmail, PDO::PARAM_STR);
            $stmt->bindParam(':vendorToken', $vendorToken, PDO::PARAM_STR);
            $stmt->bindParam(':vendorData', $vendorData, PDO::PARAM_STR);
            $stmt->bindParam(':expireAt', $expireAt, PDO::PARAM_STR);
            $stmt->execute();
        } catch(PDOException $ex) {
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
        $sql = "DELETE FROM `{$this->tableName}` WHERE `vendor_email` = :email AND `vendor_name` = :vendor LIMIT 1";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bindParam(':email', $emailAddress, PDO::PARAM_STR);
        $stmt->bindParam(':vendor', $vendorName, PDO::PARAM_STR);
        $stmt->execute();
    }
}
