<?php

return [
    'database' => [
        'type' =>'mysql',
        'host' => 'localhost',
        'database' => 'Carrot_chain',
        'username' => 'Carrot',
        'password' => 'Carrot',

        'logging' => true,
        'error' => PDO::ERRMODE_EXCEPTION
    ],
    'table' => 'wordstest',
];