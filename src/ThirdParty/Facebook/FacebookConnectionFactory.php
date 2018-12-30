<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 */

namespace TSK\SSO\ThirdParty\Facebook;

use TSK\SSO\ThirdParty\VendorConnectionFactory;
use TSK\SSO\Storage\ThirdPartyStorageRepository;

/**
 * @package TSK\SSO\ThirdParty\Facebook
 */
class FacebookConnectionFactory implements VendorConnectionFactory
{
    /**
     * @var ThirdPartyStorageRepository
     */
    private $storageRepository;

    /**
     * @var string
     */
    private $defaultGraphVersion;

    /**
     * @var string
     */
    private $permissions;

    public function __construct(
        ThirdPartyStorageRepository $storageRepository,
        $defaultGraphVersion = 'v2.12',
        $permissions = 'public_profile,email'
    ) {
        $this->storageRepository = $storageRepository;
        $this->defaultGraphVersion = $defaultGraphVersion;
        $this->permissions = $permissions;
    }

    /**
     * @param string $clientId the client id which can be generated at the third party portal
     * @param string $clientSecret this can be found similar to the clientId
     * @param string $callbackUrl the url to callback after a third party auth attempt
     * @return VendorConnection
     */
    public function get($clientId, $clientSecret, $callbackUrl)
    {
        return new FacebookConnection(
            new FacebookApiConfiguration(
                $this->defaultGraphVersion,
                $clientId,
                $clientSecret,
                $this->permissions,
                $callbackUrl
            ),
            $this->storageRepository
        );
    }
}
