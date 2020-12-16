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
use PDOException;
use PDO;

/**
 * @codeCoverageIgnore
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
    private $table;

    /**
     * @param PDO $dbConnection
     * @param string $table name of the table in the MySQL database
     */
    public function __construct(PDO $dbConnection, $table = 'thirdparty_connections')
    {
        $this->dbConnection = $dbConnection;
        $this->table = $table;
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
            $sql = "SELECT * FROM `{$this->table}` WHERE `vendor_email` = :email LIMIT 1";
            $stmt = $this->dbConnection->prepare($sql);
        } else {
            $sql = "SELECT * FROM `{$this->table}` WHERE `vendor_email` = :email AND `vendor_name` = :vendor LIMIT 1";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindParam(':vendor', $vendorName, PDO::PARAM_STR);
        }

        $stmt->bindParam(':email', $emailAddress, PDO::PARAM_STR);
        $stmt->execute();

        $userMap = $stmt->fetch(PDO::FETCH_ASSOC);
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
     * Returns any vendor MappedUser list for a given Application user.
     *
     * @param AppUser $appUser
     * @return MappedUser[]
     */
    public function getByAppUser(AppUser $appUser)
    {
        $stmt = $this->dbConnection->prepare("SELECT * FROM `{$this->table}` WHERE `app_user_id` = :appUserId");
        $appUserId = $appUser->id();
        $stmt->bindParam(':appUserId', $appUserId, is_numeric($appUserId) ? PDO::PARAM_STR : PDO::PARAM_INT);
        $stmt->execute();

        $connections = array();
        while ($mappedUser = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
        $sql = <<<SQL
            INSERT INTO `{$this->table}`
            (
                `app_user_id`,
                `vendor_name`,
                `vendor_email`,
                `vendor_access_token`,
                `vendor_refresh_token`,
                `vendor_data`,
                `created_at`
            )
            VALUES
            (
                :appUserId,
                :vendorName,
                :vendorEmail,
                :vendorToken,
                :vendorRefreshToken,
                :vendorData,
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                `vendor_access_token` = VALUES(`vendor_access_token`),
                `vendor_refresh_token` = VALUES(`vendor_refresh_token`),
                `vendor_data` = VALUES(`vendor_data`),
                `updated_at` = NOW()
SQL;
        try {
            $vendorData = json_encode($thirdPartyUser->toArray());
            $appUserId = $appUser->id();
            $vendorName = $accessToken->vendor();
            $vendorEmail = $thirdPartyUser->email();
            $vendorToken = $accessToken->token();
            $vendorRefreshToken = $accessToken->getRefreshToken();

            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindParam(':appUserId', $appUserId, PDO::PARAM_STR);
            $stmt->bindParam(':vendorName', $vendorName, PDO::PARAM_STR);
            $stmt->bindParam(':vendorEmail', $vendorEmail, PDO::PARAM_STR);
            $stmt->bindParam(':vendorToken', $vendorToken, PDO::PARAM_STR);
            $stmt->bindParam(':vendorRefreshToken', $vendorRefreshToken, PDO::PARAM_STR);
            $stmt->bindParam(':vendorData', $vendorData, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $ex) {
            throw new DataCannotBeStoredException(
                sprintf("Couldn't save the third party user data. Error : %s", $ex->getMessage()),
                $ex->getCode(),
                $ex
            );
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
        $sql = "DELETE FROM `{$this->table}` WHERE `vendor_email` = :email AND `vendor_name` = :vendor LIMIT 1";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bindParam(':email', $emailAddress, PDO::PARAM_STR);
        $stmt->bindParam(':vendor', $vendorName, PDO::PARAM_STR);
        return $stmt->execute();
    }
}
