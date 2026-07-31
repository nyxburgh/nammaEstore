<?php
// config/providers.php
//
// Which concrete provider backs each interface, plus its credentials.
// Switching gateways: change the "active" key here. Nothing in
// Controllers or Services needs to change — they only ever talk to
// App\Core\Interfaces\*, resolved via App\Core\ProviderFactory.
//
// Real credentials should come from environment variables in
// production, not be committed here — getenv() falls through to the
// empty-string placeholders in development.

return [

    'payment' => [
        'active' => 'razorpay', // razorpay | phonepe | cashfree | payu | stripe

        'razorpay' => [
            'key_id'     => getenv('RAZORPAY_KEY_ID') ?: '',
            'key_secret' => getenv('RAZORPAY_KEY_SECRET') ?: '',
        ],
    ],

    'sms' => [
        'active' => 'msg91', // msg91 | fast2sms | textlocal

        'msg91' => [
            'auth_key'  => getenv('MSG91_AUTH_KEY') ?: '',
            'sender_id' => getenv('MSG91_SENDER_ID') ?: 'NAMMAE',
        ],
    ],

    'email' => [
        'active' => 'smtp', // smtp | ses | mailgun | brevo

        'smtp' => [
            'host'       => getenv('SMTP_HOST') ?: '',
            'port'       => (int) (getenv('SMTP_PORT') ?: 587),
            'username'   => getenv('SMTP_USERNAME') ?: '',
            'password'   => getenv('SMTP_PASSWORD') ?: '',
            'from_email' => getenv('SMTP_FROM_EMAIL') ?: 'no-reply@nammaestore.test',
            'from_name'  => 'Namma E Store',
        ],
    ],

    'whatsapp' => [
        'active' => 'meta', // meta | gupshup | interakt

        'meta' => [
            'phone_number_id' => getenv('WA_PHONE_NUMBER_ID') ?: '',
            'access_token'    => getenv('WA_ACCESS_TOKEN') ?: '',
        ],
    ],

    'shipping' => [
        'active' => 'manual', // manual | shiprocket | delhivery

        'manual' => [], // no credentials needed — seller/admin enters tracking info directly
    ],

];
