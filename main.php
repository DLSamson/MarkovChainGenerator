<?php



error_reporting(E_ALL);
require_once 'vendor/autoload.php';
require_once 'Classes/Logger.class.php';
require_once 'Classes/TextGenerator.class.php';

use Medoo\Medoo;
const DEBUG_MODE = true;

$log = new Logger('./Logs/', DEBUG_MODE);
$log->startTime();
$config = require_once 'config.php';
$db = new Medoo($config['database']);
$tg = new TextGenerator($log, $db, $config['table']);


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
//$db->insert($config['table'], $data);
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
$log->write($db->last());*/





$collectData['TextArray'] = function ($json) {
    $data = json_decode($json, true);
    return $data[0];
};
$collectData['Reviews'] = function($json) {
    $json = json_decode($json, true);
    $json = $json['Companies'];
    $result = array();
    foreach ($json as $value) {
        $reviews = $value['Reviews'];
        foreach ($reviews as $review) {
            $result[] = $review['Text'];
        }
    }
    return $result;
};

//$log->write('Сгенерированный текст: '.$tg->generateSentence(), Logger::Strong);
$tg->collectData('Data/exportFull1000-1500.json', $collectData['Reviews']);



$log->write('Execution completed');
$log->endTime();
//@TODO Сделать цепной буфер, чтобы слова не повторялись как у робота;
//@TODO Правила составления предложения русского языка
//@TODO Опции генерации предложений
//@TODO Проверять значения на наличие в базе и обновлять их

// Архитектура базы данных для цепи Маркова
// @TODO Сделать таймер выполнения