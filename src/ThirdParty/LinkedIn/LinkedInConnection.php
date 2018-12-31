<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 31-12-2018
 */

namespace TSK\SSO\ThirdParty\LinkedIn;

use Happyr\LinkedIn\Exception\LinkedInException;
use TSK\SSO\ThirdParty;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\ThirdPartyUser;
use TSK\SSO\ThirdParty\VendorConnection;

/**
 * @package TSK\SSO\ThirdParty\LinkedIn
 */
class LinkedInConnection implements VendorConnection
{
    /**
     * @var LinkedInApiConfiguration
     */
    private $linkedInApiConfiguration;

    /**
     * @var LinkedIn
     */
    private $linkedin;

    /**
     * @param LinkedInApiConfiguration $linkedinApiConfiguration
     */
    public function __construct(LinkedInApiConfiguration $linkedinApiConfiguration)
    {
        $this->linkedinApiConfiguration = $linkedinApiConfiguration;
        $this->linkedin = new LinkedIn(
            $this->linkedinApiConfiguration->appId(),
            $this->linkedinApiConfiguration->appSecret()
        );
    }

    /**
     * Use this to get a link to redirect a user to the google login page.
     *
     * @return string
     */
    public function getGrantUrl()
    {
        return $this->linkedin->getLoginUrl(
            array('redirect_uri' => $this->linkedinApiConfiguration->redirectUrl())
        );
    }

    /**
     * Grants a new access token
     *
     * @return CommonAccessToken
     * @throws ThirdPartyConnectionFailedException
     */
    public function grantNewAccessToken()
    {
        try {
            return new CommonAccessToken(
                $this->linkedin->accessToken()->getToken(),
                ThirdParty::LINKEDIN
            );
        } catch(LinkedInException $ex) {
            throw new ThirdPartyConnectionFailedException(
                sprintf('Failed to establish a new third party vendor connection. Error : %s', $ex->getMessage()),
                $ex->getCode(),
                $ex
            );
        }
    }

    /**
     * Get the current authenticated user data using there existing granted token
     *
     * @param CommonAccessToken $accessToken
     * @return ThirdPartyUser
     * @throws NoThirdPartyEmailFoundException
     * @throws ThirdPartyConnectionFailedException
     */
    public function getSelf(CommonAccessToken $accessToken)
    {
        try {
            $user = $this->linkedin->get('v1/people/~:(firstName,lastName,email-address,id)');
            if (empty($user['emailAddress'])) {
                throw new NoThirdPartyEmailFoundException('An email address cannot be found from vendor');
            }

            return new ThirdPartyUser(
                $user['id'],
                sprintf('%s %s', $user['firstName'], $user['lastName']),
                $user['emailAddress']
            );
        } catch(LinkedInException $ex) {
            throw new ThirdPartyConnectionFailedException(
                sprintf('Failed to establish a new third party vendor connection. Error : %s', $ex->getMessage()),
                $ex->getCode(),
                $ex
            );
        }
    }

    /**
     * Use this to revoke the access to the third party data. this will completely remove the access from the vendor side.
     *
     * @param CommonAccessToken $accessToken
     * @return bool
     */
    public function revokeAccess(CommonAccessToken $accessToken)
    {
        $this->linkedin->clearStorage();
        return true;
    }
}
