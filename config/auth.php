<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'employees',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Here you may define every authentication guard for your application.
    | The default configuration uses session storage and the Eloquent
    | authentication provider.
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'employees', // use employees provider
        ],

        'api' => [
            'driver' => 'token',
            'provider' => 'employees',
            'hash' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication providers define how users are retrieved from
    | your database or other storage systems.
    |
    */

    'providers' => [
        'employees' => [
            'driver' => 'eloquent',
            'model' => App\Models\Employee::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Reset Settings
    |--------------------------------------------------------------------------
    |
    | Here you may define password reset settings for each user type.
    |
    */

    'passwords' => [
        'employees' => [
            'provider' => 'employees',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | The number of seconds before password confirmation times out.
    |
    */

    'password_timeout' => 10800,

];

