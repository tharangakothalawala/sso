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
use mysqli;

/**
 * @codeCoverageIgnore
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
    private $table;

    /**
     * @param mysqli $dbConnection
     * @param string $table name of the table in the MySQL database
     */
    public function __construct(mysqli $dbConnection, $table = 'thirdparty_connections')
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
            $stmt = $this->dbConnection->prepare(<<<SQL
                SELECT
                    `app_user_id`, `vendor_name`, `vendor_email`, `vendor_access_token`, `vendor_data`
                FROM `{$this->table}`
                WHERE
                    `vendor_email` = ?
                LIMIT 1
SQL
            );
            $stmt->bind_param('s', $emailAddress);
        } else {
            $stmt = $this->dbConnection->prepare(<<<SQL
                SELECT
                    `app_user_id`, `vendor_name`, `vendor_email`, `vendor_access_token`, `vendor_data`
                FROM `{$this->table}`
                WHERE
                    `vendor_email` = ?
                    AND `vendor_name` = ?
                LIMIT 1
SQL
            );
            $stmt->bind_param('ss', $emailAddress, $vendorName);
        }

        $stmt->execute();
        $stmt->bind_result($appUserId, $vendor, $vendorEmail, $vendorToken, $vendorData);
        $stmt->fetch();
        $stmt->close();
        if (empty($appUserId)) {
            return null;
        }

        return new MappedUser($appUserId, $vendor, $vendorEmail, $vendorToken, $vendorData);
    }

    /**
     * Returns any vendor MappedUser list for a given Application user.
     *
     * @param AppUser $appUser
     * @return MappedUser[]
     */
    public function getByAppUser(AppUser $appUser)
    {
        $stmt = $this->dbConnection->prepare(<<<SQL
            SELECT
                `app_user_id`, `vendor_name`, `vendor_email`, `vendor_access_token`, `vendor_data`
            FROM `{$this->table}`
            WHERE
                `app_user_id` = ?
SQL
        );
        $appUserId = $appUser->id();
        $stmt->bind_param(is_numeric($appUserId) ? 'i' : 's', $appUserId);
        $stmt->execute();

        $stmt->store_result();
        $vendor = $vendorEmail = $vendorToken = $vendorData = null;
        $stmt->bind_result($appUserId, $vendor, $vendorEmail, $vendorToken, $vendorData);

        $connections = array();
        while ($stmt->fetch()) {
            $connections[] = new MappedUser(
                $appUserId,
                $vendor,
                $vendorEmail,
                $vendorToken,
                $vendorData
            );
        }

        $stmt->close();

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
                `vendor_data`,
                `created_at`
            )
            VALUES
            (
                ?, ?, ?, ?, ?, NOW()
            )
            ON DUPLICATE KEY UPDATE
                `vendor_access_token` = VALUES(`vendor_access_token`),
                `vendor_data` = VALUES(`vendor_data`),
                `updated_at` = NOW()
SQL;

        $vendorData = json_encode($thirdPartyUser->toArray());
        $appUserId = $appUser->id();
        $vendorName = $accessToken->vendor();
        $vendorEmail = $thirdPartyUser->email();
        $vendorToken = $accessToken->token();

        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param('sssss', $appUserId, $vendorName, $vendorEmail, $vendorToken, $vendorData);
        $saved = $stmt->execute();
        if (!$saved) {
            throw new DataCannotBeStoredException(
                sprintf("Couldn't save the third party user data. Error : %s", $this->dbConnection->error)
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
        $sql = "DELETE FROM `{$this->table}` WHERE `vendor_email` = ? AND `vendor_name` = ? LIMIT 1";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("ss", $emailAddress, $vendorName);
        return $stmt->execute();
    }
}
