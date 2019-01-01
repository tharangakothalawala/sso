<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 31-12-2018
 *
 * Goto https://console.developers.google.com to create a test Google App which takes just 5 minutes ;)
 */

include_once __DIR__ . '/../vendor/autoload.php';

use TSK\SSO\AppUser\ExistingAppUser;
use TSK\SSO\Auth\AppUserAwarePersistingAuthenticator;
use TSK\SSO\Auth\Exception\AuthenticationFailedException;
use TSK\SSO\Auth\PersistingAuthenticator;
use TSK\SSO\Storage\Exception\DataCannotBeStoredException;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\ThirdPartyConnectionCollection;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\Exception\UnknownVendorRequestException;
use TSK\SSO\ThirdParty\Google\GoogleConnectionFactory;
use TSK\SSO\ThirdParty;

session_start();

$userId = !empty($_SESSION['userId']) ? $_SESSION['userId'] : null;
$vendorName = !empty($_GET['vendor']) ? $_GET['vendor'] : 'google';
$type = !empty($_GET['type']) ? $_GET['type'] : 'signin';

// using 'ThirdPartyConnectionCollection' as a helper, you can add multiple vendors
$connectionFactoryCollection = new ThirdPartyConnectionCollection();
$googleConnectionFactory = new GoogleConnectionFactory();
$connectionFactoryCollection->add(
    ThirdParty::GOOGLE,
    $googleConnectionFactory->get(
        'google_client_id',
        'google_client_secret',
        'http://example.com/examples/index.php?vendor=google&type=grant'
    )
);

try {
    $thirdPartyConnection = $connectionFactoryCollection->getByVendor($vendorName);
} catch (UnknownVendorRequestException $ex) {
    die('The requested vendor connection is not yet available!');
}

$exampleAppUserRepository = new ExampleAppUserRepository();

switch ($type) {
    case 'signin':
        header("Location : {$thirdPartyConnection->getGrantUrl()}");
        break;

    case 'grant':
        // if we have a user id, it means the user is already logged in.
        // so we can connect/relate the incoming grant to this user.
        if (!empty($userId)) {
            $authenticator = new AppUserAwarePersistingAuthenticator(
                new ExistingAppUser($userId, 'email@not-so-important.com')
            );
        } else {
            $authenticator = new PersistingAuthenticator($exampleAppUserRepository);
        }

        try {
            $appUser = $authenticator->authenticate($thirdPartyConnection);
        } catch (AuthenticationFailedException $ex) {
            die("Error : {$ex->getMessage()}");
        } catch (NoThirdPartyEmailFoundException $ex) {
            die("We didn't get the permissions to view the email address");
        } catch (ThirdPartyConnectionFailedException $ex) {
            die("We couldn't connect to the third party");
        } catch (DataCannotBeStoredException $ex) {
            die("Error : {$ex->getMessage()}");
        } catch (Exception $ex) {
            die("Error : {$ex->getMessage()}");
        }

        if (!empty($userId)) {
            header("Location : http://example.com/examples/home.php?message=new-vendor-connected");
            break;
        }

        // log the detected application's user in
        $_SESSION['userId'] = $appUser->id();

        if ($appUser->isExistingUser()) {
            header("Location : http://example.com/examples/home.php?message=welcome-back");
        } else {
            header("Location : http://example.com/examples/home.php?message=thanks-for-registering");
        }
        break;

    case 'revoke':
        $thirdPartyConnection->revokeAccess(
            new CommonAccessToken('token_that_you_want_revoke', 'google', 'vendor_email')
        );
        header("Location : http://example.com/examples/home.php?message=vendor-disconnected");
        break;
}
