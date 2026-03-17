<?php

return [
    'driver' => env('SMS_DRIVER', 'null'),

    'drivers' => [
        'semaphore' => [
            'key' => env('SMS_KEY'),
            'sender' => env('SMS_SENDER', 'SEMAPHORE'),
        ],

        'textbee' => [
            'key' => env('SMS_KEY'),
            'device_id' => env('SMS_DEVICE_ID'),
        ],

        'null' => [
            'enabled' => true,
        ],
    ],
];
