# TSK Single Sign On
This is a library which can provision new accounts and to authenticate users utilizing third party vendor connections.

[![Build Status](https://travis-ci.org/tharangakothalawala/sso.svg?branch=master)](https://travis-ci.org/tharangakothalawala/sso)
[![Total Downloads](https://poser.pugx.org/tharangakothalawala/sso/d/total.svg)](https://packagist.org/packages/tharangakothalawala/sso)
[![Latest Stable Version](https://poser.pugx.org/tharangakothalawala/sso/v/stable.svg)](https://packagist.org/packages/tharangakothalawala/sso)
[![License](https://poser.pugx.org/laravel/framework/license.svg)](https://packagist.org/packages/tharangakothalawala/sso)

# Structure

There are three(3) main functionalities.

 * Third Party Login action
 * Authentication / Authorization process
 * Revoking access to your client application

## Third Party Login action

Use the following code to redirect a user to the vendor's login page. The following uses Google as an example.

```php
use TSK\SSO\ThirdParty\Google\GoogleConnectionFactory;

$googleConnectionFactory = new GoogleConnectionFactory();
$googleConnection = $googleConnectionFactory->get(
    'google_client_id',
    'google_client_secret',
    'http://www.your-amazing-app.com/sso/google/grant'
);

header("Location: $googleConnection->getGrantUrl()");
```

## Authentication / Authorization process

Use the following code to do a signup/signin attempt. The following uses Google as an example. Please note that you will have to implement the `TSK\SSO\AppUser\AppUserRepository` to provision and validate users according to your application logic.

#### DefaultAuthenticator Usage

```php
use TSK\SSO\Auth\DefaultAuthenticator;
use TSK\SSO\Auth\Exception\AuthenticationFailedException;
use TSK\SSO\ThirdParty\Exception\NoThirdPartyEmailFoundException;
use TSK\SSO\ThirdParty\Exception\ThirdPartyConnectionFailedException;
use TSK\SSO\ThirdParty\Google\GoogleConnectionFactory;
use YouApp\TSKSSO\YourImplementationOfTheAppUserRepository;

$googleConnectionFactory = new GoogleConnectionFactory();
$googleConnection = $googleConnectionFactory->get(
    'google_client_id',
    'google_client_secret',
    'http://www.your-amazing-app.com/sso/google/grant'
);

$authenticator = new DefaultAuthenticator(
    $googleConnection,
    new YourImplementationOfTheAppUserRepository()
);

try {
    $appUser = $authenticator->signin();
} catch (ThirdPartyConnectionFailedException $ex) {
} catch (NoThirdPartyEmailFoundException $ex) {
} catch (AuthenticationFailedException $ex) {
} catch (\Exception $ex) {
}

// log the detected application's user in
$_SESSION['userid'] = $appUser->id();
```

Please note that using the `TSK\SSO\Auth\DefaultAuthenticator` will just do a simple lookup of the user store using your logic. If you want to support multiple vendors and to avoid creating new users per each of their specific email address, you will have to use this `TSK\SSO\Auth\PersistingAuthenticator`.

#### PersistingAuthenticator Usage

This uses File Sytem by default as the storge for the user mappings.

```php
use TSK\SSO\Auth\PersistingAuthenticator;
use YouApp\TSKSSO\YourImplementationOfTheAppUserRepository;

$authenticator = new PersistingAuthenticator(
    $googleConnection,
    new YourImplementationOfTheAppUserRepository()
);
```

However there are two classes available for you to use MySQL as the storage.

* `TSK\SSO\Storage\PdoThirdPartyStorageRepository`

```php
use TSK\SSO\Auth\PersistingAuthenticator;
use TSK\SSO\Storage\PdoThirdPartyStorageRepository;
use YouApp\TSKSSO\YourImplementationOfTheAppUserRepository;

$authenticator = new PersistingAuthenticator(
    $googleConnection,
    new YourImplementationOfTheAppUserRepository(),
    new PdoThirdPartyStorageRepository(<An Active PDO Connection>, 'Optional Table Name (default:thirdparty_connections)'), // \DB::connection()->getPdo() In Laravel
);
```

* `TSK\SSO\Storage\MysqliThirdPartyStorageRepository`

```php
use TSK\SSO\Auth\PersistingAuthenticator;
use TSK\SSO\Storage\PdoThirdPartyStorageRepository;
use YouApp\TSKSSO\YourImplementationOfTheAppUserRepository;

$authenticator = new PersistingAuthenticator(
    $googleConnection,
    new YourImplementationOfTheAppUserRepository(),
    new MysqliThirdPartyStorageRepository(new mysqli('localhost', 'foo', 'bar', 'db')),
);
```

Of course you can also use your own storage by just implementing this interface : `TSK\SSO\Storage\ThirdPartyStorageRepository`.

## Revoking access to your client application

In order to revoke your app from the vendor, you must have an active access token.

```php
use TSK\SSO\ThirdParty\CommonAccessToken;
use TSK\SSO\ThirdParty\Google\GoogleConnectionFactory;

$googleConnectionFactory = new GoogleConnectionFactory();
$googleConnection = $googleConnectionFactory->get(
    'google_client_id',
    'google_client_secret',
    'http://www.your-amazing-app.com/sso/google/grant'
);

$googleConnection->revokeAccess(
    new CommonAccessToken('token_that_you_want_revoke', 'google', 'vendor_email')
);
```

## Connecting multiple accounts while logged in.

 * A user may have multiple accounts on one(1) vendor.
ex: Multiple Facebook/Google accounts with different email addresses.

 * Or a user can have accounts on other vendors such as Facebook and Google at the same time. You may want to let them connect other accounts to make it easier for them to authenticate/access using multiple vendors.

You can use the `TSK\SSO\Auth\AppUserAwarePersistingAuthenticator` to validate the account that they selecting.

```php
use TSK\SSO\AppUser\AppUser;
use TSK\SSO\Auth\AppUserAwarePersistingAuthenticator;
use TSK\SSO\Auth\PersistingAuthenticator;
use YouApp\TSKSSO\YourImplementationOfTheAppUserRepository;

$userId = $_SESSION['userid'];
if (!is_null($userId)) {
    $authenticator = new AppUserAwarePersistingAuthenticator(
        $googleConnection,
        new AppUser($userId, 'current-loggedin-user-email@tsk.com')
    );
} else {
    $authenticator = new PersistingAuthenticator(
        $googleConnection,
        new YourImplementationOfTheAppUserRepository()
    );
}
```


# What Next?

To add any missing vendor support and any other storage systems.
