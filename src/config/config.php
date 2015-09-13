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

    'url' => 'https://app.klantenbinder2.nl/api/',

    /*
    |--------------------------------------------------------------------------
    | Hello Dialog API Key
    |--------------------------------------------------------------------------
    |
    | The Hello Dialog API expects this token for the account to which the
    | app is registered. It should be a 32 character hexadecimal string
    |
    */

    'token' => '',

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

];
