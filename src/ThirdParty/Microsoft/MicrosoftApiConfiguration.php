<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 02-02-2019
 */

namespace TSK\SSO\ThirdParty\Microsoft;

/**
 * @package TSK\SSO\ThirdParty\Microsoft
 */
class MicrosoftApiConfiguration
{
    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $redirectUrl;

    /**
     * MicrosoftApiConfiguration constructor.
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUrl
     */
    public function __construct($clientId, $clientSecret, $redirectUrl)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return string
     */
    public function clientId()
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function clientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @return string
     */
    public function redirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * This is just to identify that, we initiated the login sequence (not someone else)
     *
     * @return string
     */
    public function ourSecretState()
    {
        return 'dfeb6ef625880832f61c6f4bd737e11b';
    }
}
