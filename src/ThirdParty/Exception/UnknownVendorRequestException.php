<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 31-12-2018
 */

namespace TSK\SSO\ThirdParty\Exception;

use InvalidArgumentException;

/**
 * @package TSK\SSO\ThirdParty\Exception
 *
 * This is thrown when the requested connection is not known
 */
class UnknownVendorRequestException extends InvalidArgumentException
{
}
