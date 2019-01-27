<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 27-01-2019
 */

namespace TSK\SSO\Storage;

use TSK\SSO\AppUser\AppUser;
use TSK\SSO\Storage\Exception\DataCannotBeStoredException;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\ThirdPartyUser;
use MongoDB\BSON\ObjectID;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Exception\Exception;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Manager as MongoManager;
use MongoDB\Driver\Query;

/**
 * @codeCoverageIgnore
 * @package TSK\SSO\Storage
 *
 * This will connect to a MongoDB database using the pecl/mongodb driver to persist vendor data.
 * The collection will be created on the fly.
 */
class PeclMongoDbThirdPartyStorageRepository implements ThirdPartyStorageRepository
{
    /**
     * @var MongoManager
     */
    private $mongoManager;

    /**
     * @var string name of the database in the MongoDB server
     */
    private $dbName;

    /**
     * @var string name of the collection in the application database
     */
    private $collection;

    /**
     * @param MongoManager $mongoManager
     * @param string $dbName
     * @param string $collection [optional] name of the collection in the MongoDB database
     */
    public function __construct(MongoManager $mongoManager, $dbName, $collection = 'thirdparty_connections')
    {
        $this->mongoManager = $mongoManager;
        $this->dbName = $dbName;
        $this->collection = $collection;
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
            $cursor = $this->mongoManager->executeQuery(
                $this->collectionName(),
                new Query(array(
                    'vendor_email' => $emailAddress,
                ))
            );
        } else {
            $cursor = $this->mongoManager->executeQuery(
                $this->collectionName(),
                new Query(array(
                    'vendor_email' => $emailAddress,
                    'vendor_name' => $vendorName,
                ))
            );
        }

        foreach ($cursor as $mappedUserDoc) {
            return new MappedUser(
                $mappedUserDoc->app_user_id,
                $mappedUserDoc->vendor_name,
                $mappedUserDoc->vendor_email,
                $mappedUserDoc->vendor_access_token,
                $mappedUserDoc->vendor_data
            );
        }

        return null;
    }

    /**
     * Returns any vendor MappedUser list for a given Application user.
     *
     * @param AppUser $appUser
     * @return MappedUser[]
     * @throws InvalidArgumentException
     */
    public function getByAppUser(AppUser $appUser)
    {
        $cursor = $this->mongoManager->executeQuery(
            $this->collectionName(),
            new Query(array('app_user_id' => $appUser->id()))
        );

        $connections = array();
        foreach ($cursor as $mappedUserDoc) {
            $connections[] = new MappedUser(
                $mappedUserDoc->app_user_id,
                $mappedUserDoc->vendor_name,
                $mappedUserDoc->vendor_email,
                $mappedUserDoc->vendor_access_token,
                $mappedUserDoc->vendor_data
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
        $isUserHasConnection = $this->isUserHasConnection(
            $appUser->id(),
            $accessToken->vendor(),
            $thirdPartyUser->email()
        );

        $bulk = new BulkWrite();

        if ($isUserHasConnection) {
            $bulk->update(
                array(
                    'app_user_id' => $appUser->id(),
                    'vendor_name' => $thirdPartyUser->email(),
                    'vendor_email' => $accessToken->token(),
                ),
                array(
                    '$set' => array(
                        'vendor_access_token' => $accessToken->token(),
                        'vendor_data' => json_encode($thirdPartyUser->toArray()),
                    ),
                )
            );
        } else {
            $bulk->insert(array(
                '_id' => new ObjectID(),
                'app_user_id' => $appUser->id(),
                'vendor_name' => $accessToken->vendor(),
                'vendor_email' => $thirdPartyUser->email(),
                'vendor_access_token' => $accessToken->token(),
                'vendor_data' => json_encode($thirdPartyUser->toArray()),
                'created_at' => date('Y-m-d H:i:s'),
            ));
        }

        try {
            $this->mongoManager->executeBulkWrite($this->collectionName(), $bulk);
        } catch (Exception $ex) {
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
        $bulk = new BulkWrite();
        $bulk->delete(array('vendor_email' => $emailAddress, 'vendor_name' => $vendorName));

        $writeResult = $this->mongoManager->executeBulkWrite($this->collectionName(), $bulk);

        return $writeResult->getDeletedCount() === 1;
    }

    /**
     * Check if the given user has a mapping to a requested vendor connection.
     *
     * @param string $appUserId
     * @param string $vendorName
     * @param string $vendorEmail
     * @return bool
     */
    private function isUserHasConnection($appUserId, $vendorName, $vendorEmail)
    {
        $cursor = $this->mongoManager->executeQuery(
            $this->collectionName(),
            new Query(array(
                'app_user_id' => $appUserId,
                'vendor_name' => $vendorName,
                'vendor_email' => $vendorEmail,
            ))
        );

        foreach ($cursor as $_) {
            return true;
        }

        return false;
    }

    /**
     * Returns a namespaced collection name.
     *
     * @return string
     */
    private function collectionName()
    {
        return sprintf('%s.%s', $this->dbName, $this->collection);
    }
}
