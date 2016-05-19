<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hello Dialog API location
    |--------------------------------------------------------------------------
    |
    | The Hello Dialog API address.
    | Requires a trailing slash.
    |
    */

    'url' => env('HELLODIALOG_API_URL', 'https://app.hellodialog.com/api'),

    /*
    |--------------------------------------------------------------------------
    | Hello Dialog API Key
    |--------------------------------------------------------------------------
    |
    | The Hello Dialog API expects this token for the account to which the
    | app is registered. It should be a 32 character hexadecimal string
    |
    */

    'token' => env('HELLODIALOG_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Sender / From Details
    |--------------------------------------------------------------------------
    |
    | The sender that will be used when the HelloDialog transactional mails
    | are sent out. This should probably be a standard no-reply address.
    |
    */
    'sender' => [
        'email' => 'no-reply@your-app.com',
        'name'  => 'Your App',
    ],

    /*
    |--------------------------------------------------------------------------
    | Templates
    |--------------------------------------------------------------------------
    |
    | The default template set will be used by the SwiftMailer implementation
    | and the default mailing methods (expecting a simple message replacement).
    |
    | Other templates may be defined here.
    |
    */

    'default_template' => 'transactional',

    'templates' => [

        'transactional' => [
            'id' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | Whether to send mail through the queue by default, or, if set to false,
    | to send synchronized instead.
    |
    */

    'queue' => false,

    /*
    |--------------------------------------------------------------------------
    | Debugging Options
    |--------------------------------------------------------------------------
    |
    | In order to debug mailings, you can opt to enable debug logging of relevant
    | transactions, or prevent mailings from going out by 'mocking' them.
    |
    | 'mock' will log the mailing content to the log instead.
    |
    */

    'debug' => env('HELLODIALOG_DEBUG', false),
    'mock'  => env('HELLODIALOG_MOCK', false),

];
