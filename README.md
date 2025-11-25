# Laravel OTP Package

A lightweight and flexible Laravel package for generating and validating one-time passwords (OTP).  
Supports storage drivers (**cache** and **database**), secure hashing, configurable expiry, and customizable OTP format.

---

## Features

- Secure hashing (no plain OTP stored)
- Supports **cache** and **database** storage drivers
- Configurable OTP length and type (numeric / alphanumeric / hex)
- Expiry control (minutes or seconds)
- Pluggable repository pattern (swap storage without changing code)
- Returns structured validation response (`['valid' => bool, 'message' => string]`)
- Optional Facade: `Otp::generate()` / `Otp::validate()`
- Publishable config and (optional) migrations

---

## Installation

Install via Composer:

```bash
composer require yossivic/otp
```

## Publish Configuration

```bash
php artisan vendor:publish --tag=otp-config
```
This copies the package config to:
```.
config/otp.php
```


## Database Storage Setup (optional)
If you want persistent OTP storage (database) instead of cache (is default value):
1. Publish migrations (if you included them)

```bash
php artisan vendor:publish --tag=otp-migrations
```
This should copy migration file(s) into:
```
database/migrations/2025_xx_xx_xxxxxx_create_otps_table.php
```
2.Migrate

```bash
php artisan migrate
```

## Usage
You can use the service either by resolving from the container (dependency injection / app()) or via the facade.

The service returns OTP (string) on generation and a structured array on validation:
```
[
  'valid' => true|false,
  'message' => '...'
]
```

1) Using Dependency Injection (recommended)

```php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yossivic\Otp\Services\OtpService;

class OtpController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function generate(Request $request)
    {
        $identifier = $request->input('identifier'); // e.g. user id, email, phone
        $otp = $this->otpService->generate($identifier);

        // Send OTP to user via your chosen channel (outside this package)
        return response()->json(['otp' => $otp]);
    }

    public function validate(Request $request)
    {
        $identifier = $request->input('identifier');
        $code = $request->input('otp');

        $result = $this->otpService->validate($identifier, $code);

        if ($result['valid']) {
            return response()->json(['message' => 'OTP valid']);
        }

        return response()->json(['message' => $result['message']], 422);
    }
}
```

Manual resolution (anywhere in code):
```php
$otpService = app(\Yossivic\Otp\Services\OtpService::class);

$otp = $otpService->generate('user_123');           // returns generated OTP (string)
$result = $otpService->validate('user_123', '123456'); // returns ['valid'=>..., 'message'=>...]
```

2) Using the Facade (shortcut)

```php
use Otp;

$otp = Otp::generate('user_123');

$result = Otp::validate('user_123', '123456');

if ($result['valid']) {
    // success
} else {
    // $result['message'] explains the failure (expired, not found, max attempts, invalid)
}
```

## Repository switching (cache vs db)
Switch storage by editing 
```
config/otp.php:

// cache (fast, recommended)
'repository' => 'cache',

// or for persistent storage
'repository' => 'db',
```
## Contributing

I am not perfect. please open an issue to discuss major features or you have any suggestion to improve.
