# TSK Single Sign On
This is a library which can provision new accounts and can authenticate users utilizing third party vendor connections.

[![Latest Stable Version](https://poser.pugx.org/tharangakothalawala/sso/v/stable.svg)](https://packagist.org/packages/tharangakothalawala/sso)
[![License](https://poser.pugx.org/laravel/framework/license.svg)](https://packagist.org/packages/tharangakothalawala/sso)
[![Build Status](https://travis-ci.org/tharangakothalawala/sso.svg?branch=master)](https://travis-ci.org/tharangakothalawala/sso)
[![Quality Score](https://img.shields.io/scrutinizer/g/tharangakothalawala/sso.svg?style=flat-square)](https://scrutinizer-ci.com/g/tharangakothalawala/sso)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/tharangakothalawala/sso.svg?style=flat-square)](https://scrutinizer-ci.com/g/tharangakothalawala/sso)
[![Total Downloads](https://poser.pugx.org/tharangakothalawala/sso/d/total.svg)](https://packagist.org/packages/tharangakothalawala/sso)

# Supported Vendors

* Amazon
* Facebook
* GitHub
* Google
* LinkedIn
* Slack
* Spotify
* Twitter
* Yahoo

# Structure

There are three(3) main functions.

 * Third Party Login action
 * Authentication process
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

## Authentication process

Use the following code to do a signup/signin. The following uses Google as an example. Please note that you will have to implement the `TSK\SSO\AppUser\AppUserRepository` to provision and validate users according to your application logic.
See my example in the `examples` directory.

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
    new YourImplementationOfTheAppUserRepository()
);

try {
    $appUser = $authenticator->authenticate($googleConnection);
} catch (AuthenticationFailedException $ex) {
} catch (DataCannotBeStoredException $ex) {
} catch (NoThirdPartyEmailFoundException $ex) {
} catch (ThirdPartyConnectionFailedException $ex) {
} catch (\Exception $ex) {
}

// log the detected application's user in
$_SESSION['userId'] = $appUser->id();
```

Please note that using the `TSK\SSO\Auth\DefaultAuthenticator` will just do a simple lookup of the user store using your logic. If you want to support multiple vendors and to avoid creating new users per each of their specific email address, you will have to use this `TSK\SSO\Auth\PersistingAuthenticator`.

#### PersistingAuthenticator Usage

This uses File System by default as the storage for the user mappings.

```php
use TSK\SSO\Auth\PersistingAuthenticator;
use YouApp\TSKSSO\YourImplementationOfTheAppUserRepository;

$authenticator = new PersistingAuthenticator(
    new YourImplementationOfTheAppUserRepository()
);
```

##### MySQL

There are two classes available for you to use MySQL as the storage.

For MySQL, I have provided a schema file under sql folder. Please use that.

* `TSK\SSO\Storage\PdoThirdPartyStorageRepository`

```php
use TSK\SSO\Auth\PersistingAuthenticator;
use TSK\SSO\Storage\PdoThirdPartyStorageRepository;
use YouApp\TSKSSO\YourImplementationOfTheAppUserRepository;

$authenticator = new PersistingAuthenticator(
    new YourImplementationOfTheAppUserRepository(),
    new PdoThirdPartyStorageRepository(
        // In Laravel, you can do this to get its PDO connection: \DB::connection()->getPdo();
        new PDO('mysql:dbname=db;host=localhost', 'foo', 'bar'),
        'Optional Table Name (default:thirdparty_connections)'
    ),
);
```

* `TSK\SSO\Storage\MysqliThirdPartyStorageRepository`

```php
use TSK\SSO\Auth\PersistingAuthenticator;
use TSK\SSO\Storage\PdoThirdPartyStorageRepository;
use YouApp\TSKSSO\YourImplementationOfTheAppUserRepository;

$authenticator = new PersistingAuthenticator(
    new YourImplementationOfTheAppUserRepository(),
    new MysqliThirdPartyStorageRepository(new mysqli('localhost', 'foo', 'bar', 'db')),
);
```

##### MongoDB

* `TSK\SSO\Storage\PeclMongoDbThirdPartyStorageRepository`

```php
use TSK\SSO\Auth\PersistingAuthenticator;
use TSK\SSO\Storage\PeclMongoDbThirdPartyStorageRepository;
use YouApp\TSKSSO\YourImplementationOfTheAppUserRepository;

$authenticator = new PersistingAuthenticator(
    new YourImplementationOfTheAppUserRepository(),
    new PeclMongoDbThirdPartyStorageRepository(new MongoDB\Driver\Manager('mongodb://localhost:27017/yourdb'), 'yourdb'),
);
```

Of course you can use your own storage by just implementing this interface : `TSK\SSO\Storage\ThirdPartyStorageRepository`.

## Revoking vendor access to your client application

```php
use TSK\SSO\ThirdParty\VendorConnectionRevoker;

$vendorConnectionRevoker = new VendorConnectionRevoker(
    $googleConnection, // the vendor connection
    // [optional] `TSK\SSO\Storage\ThirdPartyStorageRepository` implementation. File system storage is used by default
);
$vendorConnectionRevoker->revoke($vendorEmail, $vendorName); // returns a bool
```

## Connecting multiple accounts while logged in.

 * A user may have multiple accounts on one(1) vendor.
ex: Multiple Facebook/Google accounts with different email addresses.

 * Or a user can have accounts on other vendors such as Facebook and Google at the same time. You may want to let them connect other accounts to make it easier for them to authenticate/access using multiple vendors.

You can use the `TSK\SSO\Auth\AppUserAwarePersistingAuthenticator` to validate the account that they selecting.

```php
use TSK\SSO\AppUser\ExistingAppUser;
use TSK\SSO\Auth\AppUserAwarePersistingAuthenticator;
use TSK\SSO\Auth\PersistingAuthenticator;
use YouApp\TSKSSO\YourImplementationOfTheAppUserRepository;

$userId = $_SESSION['userid'];
if (!is_null($userId)) {
    $authenticator = new AppUserAwarePersistingAuthenticator(
        new ExistingAppUser($userId, 'current-loggedin-user-email@tsk-webdevelopment.com')
    );
} else {
    $authenticator = new PersistingAuthenticator(
        new YourImplementationOfTheAppUserRepository()
    );
}
```


# What Next?

To add any missing vendor support and any other storage systems.

# Demo

#### Creating your own apps [Optional]

I have created several demo apps and have registered them in Amazon, GitHub, Google, Twitter & Yahoo.
Optionally you may register your own apps if you want to test.

* Amazon : https://sellercentral.amazon.com/hz/home
* GitHub : https://github.com/settings/developers
* Google : https://console.developers.google.com
* Twitter : https://developer.twitter.com/en/apps - You must at least have 'Read-only' access permission and have ticked 'Request email address from users' under additional permissions.
* Spotify : https://developer.spotify.com/dashboard/applications
* Yahoo : https://developer.yahoo.com/apps - You must at least select 'Read/Write Public and Private' of 'Profiles (Social Directory)' API permissions.

#### Host File Entry

And add the `localhost.com` into the host file as following. (Linux : `/etc/hosts`, Windows: `C:\Windows\System32\drivers\etc\hosts`)

```bash
127.0.0.1    localhost.com
```

#### Start Demo
```bash
make demo
```

Then go to http://localhost.com
