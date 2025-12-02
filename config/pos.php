<?php

return [
    'base_currency' => 'TJS',

    'supported_currencies' => [
        'TJS',
        'USD',
    ],

    'default_usd_rate' => 11.0,

    'pos' => [
        'default_discount' => 0,
        'max_cart_items' => 200,
        'allow_negative_stock' => false,
    ],

    'roles' => [
        'admin' => 'admin',
        'cashier' => 'cashier',
        'accountant' => 'accountant',
    ],
];
