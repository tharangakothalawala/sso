# TSK SSO Library
This is a library which can provision new accounts and to authenticate users utilizing third party vendor connections.

[![Build Status](https://travis-ci.org/tharangakothalawala/sso.svg?branch=master)](https://travis-ci.org/tharangakothalawala/sso)
[![Total Downloads](https://poser.pugx.org/tharangakothalawala/sso/d/total.svg)](https://packagist.org/packages/tharangakothalawala/sso)
[![Latest Stable Version](https://poser.pugx.org/tharangakothalawala/sso/v/stable.svg)](https://packagist.org/packages/tharangakothalawala/sso)
[![License](https://poser.pugx.org/laravel/framework/license.svg)](https://packagist.org/packages/tharangakothalawala/sso)

# Structure

There are three(3) main functionalities.

 * Third Party Login action
 * Authentication / Authorization checks
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

## Authentication / Authorization checks

Use the following code to do a signup/signin attempt. The following uses Google as an example.

#### DefaultAuthenticator Usage

```php
use TSK\SSO\ThirdParty\Google\GoogleConnectionFactory;
use TSK\SSO\Auth\DefaultAuthenticator;

$googleConnectionFactory = new GoogleConnectionFactory();
$googleConnection = $googleConnectionFactory->get(
    'google_client_id',
    'google_client_secret',
    'http://www.your-amazing-app.com/sso/google/grant'
);

$authenticator = new DefaultAuthenticator(
    $googleConnection,
    new YourImplementationOfTheAppUserRepository() // you will have to implement this interface : TSK\SSO\AppUser\AppUserRepository according to your application logic to provision and check users.
);

try {
    $appUser = $authenticator->signin();
} catch (ThirdPartyConnectionFailedException $ex) {
} catch (NoThirdPartyEmailFoundException $ex) {
} catch (AuthenticationFailedException $ex) {
} catch (\Exception $ex) {
}

// log the detected application's user
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
use TSK\SSO\ThirdParty\Google\GoogleConnectionFactory;

$googleConnectionFactory = new GoogleConnectionFactory();
$googleConnection = $googleConnectionFactory->get(
    'google_client_id',
    'google_client_secret',
    'http://www.your-amazing-app.com/sso/google/grant'
);

$googleConnection->revokeAccess($googleConnection->grantNewAccessToken());
```
