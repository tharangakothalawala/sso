<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 31-12-2018
 */

namespace TSK\SSO\ThirdParty\LinkedIn;

use Happyr\LinkedIn\AccessToken;
use Happyr\LinkedIn\LinkedIn as LinkedInBase;

/**
 * @package TSK\SSO\ThirdParty\LinkedIn
 */
class LinkedIn extends LinkedInBase
{
    /**
     * @return AccessToken
     */
    public function accessToken()
    {
        if ($this->accessToken === null) {
            return $this->getAccessToken();
        }

        return $this->accessToken;
    }
}
