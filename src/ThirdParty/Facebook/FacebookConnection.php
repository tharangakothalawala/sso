<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 */

namespace TSK\SSO\ThirdParty\Facebook;

use TSK\SSO\Storage\ThirdPartyStorageRepository;
use TSK\SSO\ThirdParty;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\ThirdPartyUser;
use TSK\SSO\ThirdParty\VendorConnection;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;

/**
 * @codeCoverageIgnore
 * @package TSK\SSO\ThirdParty\Facebook
 * @see https://developers.facebook.com/docs/php/api/5.0.0
 */
class FacebookConnection implements VendorConnection
{
    /**
     * @var FacebookApiConfiguration
     */
    private $facebookApiConfiguration;

    /**
     * @var ThirdPartyStorageRepository
     */
    private $storageRepository;

    /**
     * @var Facebook
     */
    private $facebook;

    /**
     * @param FacebookApiConfiguration $facebookApiConfiguration
     * @param ThirdPartyStorageRepository $storageRepository
     */
    public function __construct(
        FacebookApiConfiguration $facebookApiConfiguration,
        ThirdPartyStorageRepository $storageRepository
    ) {
        $this->facebookApiConfiguration = $facebookApiConfiguration;
        $this->storageRepository = $storageRepository;
        $this->facebook = new Facebook(array(
            'app_id' => $this->facebookApiConfiguration->appId(),
            'app_secret' => $this->facebookApiConfiguration->appSecret(),
            'default_graph_version' => $this->facebookApiConfiguration->apiVersion(),
        ));
    }

    /**
     * Use this to get a link to redirect a user to the facebook login page.
     *
     * @return string
     */
    public function getGrantUrl()
    {
        $params = array('req_perms' => $this->facebookApiConfiguration->appPermissions());

        $helper = $this->facebook->getRedirectLoginHelper();

        return $helper->getLoginUrl($this->facebookApiConfiguration->redirectUrl(), $params);
    }

    /**
     * Grants a new access token
     *
     * @return CommonAccessToken
     * @throws ThirdPartyConnectionFailedException
     */
    public function grantNewAccessToken()
    {
        $helper = $this->facebook->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken($this->facebookApiConfiguration->redirectUrl());
            $oAuth2Client = $this->facebook->getOAuth2Client();
            $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);

            return new CommonAccessToken($accessToken->getValue(), ThirdParty::FACEBOOK);
        } catch (FacebookResponseException $ex) {
            throw new ThirdPartyConnectionFailedException(
                'Graph returned an error: ' . $ex->getMessage(),
                $ex->getCode(),
                $ex
            );
        } catch (FacebookSDKException $ex) {
            throw new ThirdPartyConnectionFailedException(
                'Facebook SDK returned an error: ' . $ex->getMessage(),
                $ex->getCode(),
                $ex
            );
        }
    }

    /**
     * Use this to retrieve the current user's third party user data using there existing granted access token
     *
     * @param CommonAccessToken $accessToken
     * @return ThirdPartyUser
     * @throws NoThirdPartyEmailFoundException
     * @throws ThirdPartyConnectionFailedException
     */
    public function getSelf(CommonAccessToken $accessToken)
    {
        try {
            $response = $this->facebook->get('/me?fields=id,first_name,last_name,email,gender', $accessToken->token());
            $graphUser = $response->getGraphUser();

            $thirdPartyEmail = $graphUser->getEmail();
            if (empty($thirdPartyEmail)) {
                throw new NoThirdPartyEmailFoundException('An email address cannot be found from vendor');
            }

            return new ThirdPartyUser(
                $graphUser->getId(),
                sprintf('%s %s', $graphUser->getFirstName(), $graphUser->getLastName()),
                $thirdPartyEmail,
                "http://graph.facebook.com/{$graphUser->getId()}/picture",
                $graphUser->getGender()
            );
        } catch (FacebookResponseException $ex) {
            throw new ThirdPartyConnectionFailedException(
                'Graph returned an error: ' . $ex->getMessage(),
                $ex->getCode(),
                $ex
            );
        } catch (FacebookSDKException $ex) {
            throw new ThirdPartyConnectionFailedException(
                'Facebook SDK returned an error: ' . $ex->getMessage(),
                $ex->getCode(),
                $ex
            );
        }
    }

    /**
     * Use this to revoke the access to the third party data.
     * This will completely remove the access from the vendor side.
     *
     * @param CommonAccessToken $accessToken
     * @return bool
     */
    public function revokeAccess(CommonAccessToken $accessToken)
    {
        $mappedUser = $this->storageRepository->getUser($accessToken->email(), ThirdParty::FACEBOOK);
        if (is_null($mappedUser)) {
            return false;
        }

        $vendorData = $mappedUser->decodedVendorData();
        if (empty($vendorData[ThirdPartyUser::ID])) {
            return false;
        }

        try {
            $this->facebook->delete(
                sprintf('/%s/permissions', $vendorData[ThirdPartyUser::ID]),
                array(),
                $accessToken->token()
            );
        } catch (FacebookResponseException $ex) {
            return false;
        }

        return true;
    }
}
