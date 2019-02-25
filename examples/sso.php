<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 31-12-2018
 *
 * Goto https://console.developers.google.com to create a test Google App which takes just 5 minutes ;)
 */

include_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/DemoAppUserRepository.php';

use TSK\SSO\AppUser\ExistingAppUser;
use TSK\SSO\Auth\AppUserAwarePersistingAuthenticator;
use TSK\SSO\Auth\Exception\AuthenticationFailedException;
use TSK\SSO\Auth\PersistingAuthenticator;
use TSK\SSO\Storage\Exception\DataCannotBeStoredException;
use TSK\SSO\Storage\FileSystemThirdPartyStorageRepository;
use TSK\SSO\ThirdParty;
use TSK\SSO\ThirdParty\Amazon\AmazonConnectionFactory;
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\ThirdPartyConnectionCollection;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\Exception\UnknownVendorRequestException;
use TSK\SSO\ThirdParty\GitHub\GitHubConnectionFactory;
use TSK\SSO\ThirdParty\Google\GoogleConnectionFactory;
use TSK\SSO\ThirdParty\Twitter\TwitterConnectionFactory;
use TSK\SSO\ThirdParty\Spotify\SpotifyConnectionFactory;
use TSK\SSO\ThirdParty\VendorConnectionRevoker;
use TSK\SSO\ThirdParty\Yahoo\YahooConnectionFactory;

session_start();

define('CALLBACK_URL', 'http://localhost.com/sso.php?vendor=%s&task=grant');
$userId = !empty($_SESSION['userId']) ? $_SESSION['userId'] : null;
$userEmail = !empty($_SESSION['userEmail']) ? $_SESSION['userEmail'] : 'email@not-so-important.com';
$vendorName = !empty($_GET['vendor']) ? $_GET['vendor'] : 'google';
$task = !empty($_GET['task']) ? $_GET['task'] : 'signin';

if ($task === 'logout') {
    session_destroy();
    header("Location: /index.php");
    exit;
}

// using 'ThirdPartyConnectionCollection' as a helper, you can add multiple vendors
$connectionFactoryCollection = new ThirdPartyConnectionCollection();
$googleConnectionFactory = new GoogleConnectionFactory();
$connectionFactoryCollection->add(
    ThirdParty::GOOGLE,
    $googleConnectionFactory->get(
        '156056460465-55sfaeiv4s4nehhuhcccbd27u5cfblhc.apps.googleusercontent.com', // demo real-app id
        'R8ROkwfcjanq6_SskEV287oz', // demo real-app secret
        sprintf(CALLBACK_URL, ThirdParty::GOOGLE)
    )
);
$twitterConnectionFactory = new TwitterConnectionFactory();
$connectionFactoryCollection->add(
    ThirdParty::TWITTER,
    $twitterConnectionFactory->get(
        'DFjFCIjmSwtNhBMvmfLEZPdPj', // demo real-app api key
        'ID2nVdPyImNowcDy1tZgqND2y4Z4h45fEsDh3ORKr7KcSNDiTd', // demo real-app api secret
        sprintf(CALLBACK_URL, ThirdParty::TWITTER)
    )
);
$yahooConnectionFactory = new YahooConnectionFactory();
$connectionFactoryCollection->add(
    ThirdParty::YAHOO,
    $yahooConnectionFactory->get(
        'dj0yJmk9NGNuMXRLMVlZQmlhJnM9Y29uc3VtZXJzZWNyZXQmc3Y9MCZ4PTY3', // demo real-app api key
        '8dd4eb902c611d5b231e3385f8abbb54fe3fef7f', // demo real-app api secret
        sprintf(CALLBACK_URL, ThirdParty::YAHOO) // note that Yahoo doesn't support just localhost as the hostname. You may add a host entry.
    )
);
$gitHubConnectionFactory = new GitHubConnectionFactory('SSO Demo');
$connectionFactoryCollection->add(
    ThirdParty::GITHUB,
    $gitHubConnectionFactory->get(
        'bcc3b8b882be87a2cfde', // demo real-app api key
        '38c270aa36ad2700cc275f4f268fb464d79ab730', // demo real-app api secret
        sprintf(CALLBACK_URL, ThirdParty::GITHUB)
    )
);
$amazonConnectionFactory = new AmazonConnectionFactory();
$connectionFactoryCollection->add(
    ThirdParty::AMAZON,
    $amazonConnectionFactory->get(
        'amzn1.application-oa2-client.32d417021c994d9983136748ab40508f', // demo real-app api key
        'edcdbf37d4cc7af632bd6dea7be4d9ec21e30978c095982576614b29c9b6a6a3', // demo real-app api secret
        sprintf(CALLBACK_URL, ThirdParty::AMAZON)
    )
);
$spotifyConnectionFactory = new SpotifyConnectionFactory();
$connectionFactoryCollection->add(
    ThirdParty::SPOTIFY,
    $spotifyConnectionFactory->get(
        '40de952ae63b403f894e47a6314bc64d', // demo real-app client id
        '38eac415d0eb473bb0b4d1d732bc25d3', // demo real-app client secret
        sprintf(CALLBACK_URL, ThirdParty::SPOTIFY)
    )
);

try {
    $thirdPartyConnection = $connectionFactoryCollection->getByVendor($vendorName);
} catch (UnknownVendorRequestException $ex) {
    $_SESSION['error'] = "Sorry you cannot use '{$vendorName}' to proceed at the moment as it is not yet available!";
    header("Location: /index.php");
    exit;
}

$exampleAppUserRepository = new DemoAppUserRepository(__DIR__);
$storageRepository = new FileSystemThirdPartyStorageRepository(__DIR__);

switch ($task) {
    case 'signin':
        header("Location: {$thirdPartyConnection->getGrantUrl()}");
        break;

    case 'grant':
        // if we have a user id, it means the user is already logged in.
        // so we can connect/relate the incoming grant to this user.
        if (!empty($userId)) {
            $authenticator = new AppUserAwarePersistingAuthenticator(
                new ExistingAppUser($userId, $userEmail),
                $storageRepository
            );
        } else {
            $authenticator = new PersistingAuthenticator($exampleAppUserRepository, $storageRepository);
        }

        try {
            $appUser = $authenticator->authenticate($thirdPartyConnection);
        } catch (AuthenticationFailedException $ex) {
            $error = $ex->getMessage();
        } catch (NoThirdPartyEmailFoundException $ex) {
            $error = "We didn't get the permissions to view the email address.";
        } catch (ThirdPartyConnectionFailedException $ex) {
            $error = "We couldn't connect to the third party";
        } catch (DataCannotBeStoredException $ex) {
            $error = $ex->getMessage();
        } catch (Exception $ex) {
            $error = $ex->getMessage();
        }

        if (!empty($error)) {
            $_SESSION['error'] = $error;
            header("Location: /index.php");
            break;
        }

        if (!empty($userId)) {
            $_SESSION['success'] = "You have connected a new connection to '{$vendorName}'";
            header("Location: /index.php");
            break;
        }

        // log the detected application's user in
        $_SESSION['userId'] = $appUser->id();
        $_SESSION['userEmail'] = $appUser->email();
        $_SESSION['success'] = ($appUser->isExistingUser()) ? 'Welcome back!' : 'Thank you for registering!';

        header("Location: /index.php");
        break;

    case 'revoke':
        $vendorEmail = base64_decode($_GET['meta']);

        $vendorConnectionRevoker = new VendorConnectionRevoker($thirdPartyConnection, $storageRepository);
        $isRevoked = $vendorConnectionRevoker->revoke($vendorEmail, $vendorName);
        if (!$isRevoked) {
            $_SESSION['error'] = 'Cannot revoke the vendor connection!';
            header("Location: /index.php");
            break;
        }

        $_SESSION['success'] = "You have disconnected a connection to the '{$vendorName}'";
        header("Location: /index.php");
        break;
}
