# Demo

#### Creating your own apps [Optional]

I have created several demo apps and have registered them in Amazon, GitHub, Google, Twitter & Yahoo.
Optionally you may register your own apps if you want to test.

* Amazon : https://sellercentral.amazon.com/hz/home
* GitHub : https://github.com/settings/developers
* Google : https://console.developers.google.com
* Twitter : https://developer.twitter.com/en/apps - You must at least have 'Read-only' access permission and have ticked 'Request email address from users' under additional permissions.
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

### Login Page

![](images/demo_before_login.PNG?raw=true)

### Home Page

![](images/demo_after_login.PNG?raw=true)
