<?php


return [
    'office' => [
        'contact' => [
            'email' => 1,
            'phone' => 2
        ],
        'status' => [
            'active' => 1,
            'left' => 0
        ],
        'entity' => [
            'individual' => 1,
            'legal' => 2
        ]
    ],
    'additives' => [
        'punishment' => 0,
        'bounty' => 1
    ],
    'payment' => [
        'type' => [
            'main' => 1,
            'punishment' => 2,
            'bonus' => 3
        ],
        'payed' => 1,
        'no_paid' => 0
    ],
    'message' => [
        'status' => [
            'to_office' => 1,
            'from_office' => 0
        ]
    ],
    'offers' => [
        'status' => [
            'wait' => 2,
            'accept' => 1,
            'reject' => 0
        ]
    ],
    'reservation' => [
        'status' => [
            'wait' => 0,
            'set' => 1,
            'accepted' => 2,
            'rejected' => 3
        ]
    ],
    'action_type'=>[
        'in' => '1111497561',
        'out' => '1111497562',
        'other' => null
    ]
];
