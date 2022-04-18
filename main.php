<?php

error_reporting(E_ERROR);
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

//require_once 'Actions/TestData.php';

$log->write('Сгенерированный текст: '.$tg->generateSentence(), Logger::Strong);

$log->write('Execution completed');
$log->endTime();


//@TODO Сделать цепной буфер, чтобы слова не повторялись как у робота;
//@TODO Правила составления предложения русского языка
//@TODO Опции генерации предложений
//@TODO Проверять значения на наличие в базе и обновлять их
// @TODO Архитектура базы данных для цепи Маркова
// @TODO Сделать таймер выполнения
// @TODO https://yandex.ru/dev/dictionary/doc/dg/reference/lookup.html - для определения типа слова