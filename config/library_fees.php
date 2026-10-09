<?php

return [
    'reservation_default_expiry_days' => 7,

    'damage_levels' => [
        1 => ['label' => 'Warning / minor fee', 'default_amount' => 50.00],
        2 => ['label' => 'Repair fee', 'default_amount' => 200.00],
        3 => ['label' => 'Repair or replacement fee', 'default_amount' => 500.00],
        4 => ['label' => 'Condemn / lost / destroyed', 'default_amount' => 1500.00],
    ],
];
