<?php

/* @var Logger $log         */
/* @var TextGenerator $tg   */
/* @var Medoo $db           */
/* @var array $confg        */

/*
$data = [
    [
        'word' => 'hello',
        'nextWord'=> 'world!',
        'amount' => 1,
    ],
    [
        'word' => 'angry',
        'nextWord'=> 'hamster',
        'amount' => 1,
    ],
    [
        'word' => 'good',
        'nextWord'=> 'morning',
        'amount' => 1,
    ]
];
$db->insert($config['table'], $data);
$result = $db->select($config['table'], ['word', 'nextWord', 'amount'],
    [ 'OR' => [
        'AND #1' => [
            'word' => 'good',
            'nextWord'=> 'morning',
        ],
        'AND #2' => [
            'word' => 'angry',
            'nextWord'=> 'hamster',
        ],
    ]
]);
$log->write($db->last());
*/

$collectData['TextArray'] = function ($json) {
    $data = json_decode($json, true);
    $texts= array();
    foreach ($data as $value) {
        $texts[] = $value['text'];
    }
    return $texts;
};
$tg->collectData('Data/data.json', $collectData['TextArray']);