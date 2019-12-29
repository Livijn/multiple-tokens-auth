<?php

return [
    'table' => 'api_tokens',

    'token' => [
        /*
         * Amount of days token should live
         * Value is in days.
        */
        'life_length' => 60,

        /*
         * Amount of days left of life when we should extend it
         * Value is in days.
        */
        'extend_life_at' => 10,
    ],
];
