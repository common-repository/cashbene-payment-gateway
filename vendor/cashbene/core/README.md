# 🌊 Cashbene Core Gateway  

The Cashbene Core Gateway PHP library provides integration access to the REST API v1

More at [Cashbene.com]

## Requirements:
* Composer
* PHP >= 7.1
* PHP lib: ZIP


## Installation:
```sh
$ composer require cashbene/core
```

### Usage:
To use Cashbene Core, set up the Gateway in your code:
```php
use Cashbene\Core\Utils\Configuration;
use Cashbene\Core\Gateway;

$configuration = new Configuration([
	'merchant_id'   => '',          // Your merchant ID
	'client_id'     => '',          // Your client id
	'client_secret' => '',          // Your client secret
	'environment'   => 'sandbox'    // 'sandbox' or 'production'
]);
$gateway = new Gateway($configuration);
$gateway->setMerchantCredentialsCallback(function ($accessToken) {
    [...] // Save access token to database or session
});
```

### Get User data:
To retrieve a user's data, you must first log in. After logging in we will get a "ClientCredentials" object which will be needed to authorize the actions performed by the user.

```php
// Return a ClientCredentials object
$clientCredentials = $gateway->userContext()->signIn('example@domain.com', 0000); // Sign in user with email and pin

// Return a User object
$userData = $gateway->userContext()->getUserData($clientCredentials);
```

### Register new User:
To register a user, first send an SMS with the authorization code and then create a user object with the code received via SMS. After creating the object, we can register the user.

```php
use Cashbene\Core\Dto\Register\RegisterUser;
use Cashbene\Core\Dto\MobileNumber;

// Prepare user phone number object
$mobileNumber = new MobileNumber();
$mobileNumber->prefix = '+48';
$mobileNumber->number = '123456789';

// Sent sms with activation code to user
$gateway->registerContext()->generateSmsVerificationCode($mobileNumber); // return true if user was registered successfully or Exception if not

// Prepare user object
$user = new RegisterUser();
$user->email = 'example@domain.com';
$user->firstName = 'John';
$user->lastName = 'Doe';
$user->mobile = $mobileNumber;
$user->registrationCode = '123456'; // Code sent to user's mobile number
$user->consents = [...] // array of RegistrationConsents objects
[...]

// Register user
$gateway->registerContext()->signUp($user); // return true if user was registered successfully or Exception if not
```

### Maintainer:
🔨 Created by [InterSynergy.pl] \
📧 info@intersynergy.pl

[InterSynergy.pl]: <https://www.intersynergy.pl>
[Cashbene.com]: <https://cashbene.com/>
