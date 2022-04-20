<?php

error_reporting(E_ERROR | E_WARNING);
const DEBUG_MODE = true;

require_once 'vendor/autoload.php';
require_once 'Actions/init.php';

require_once 'Actions/TestData.php';
//require_once 'Actions/Otzovik.php';
//require_once 'Actions/DB_test.php';


//@TODO Сделать цепной буфер, чтобы слова не повторялись как у робота;
//@TODO Правила составления предложения русского языка
//@TODO Опции генерации предложений
//@TODO Проверять значения на наличие в базе и обновлять их
// @TODO Архитектура базы данных для цепи Маркова
// @TODO Сделать таймер выполнения
// @TODO https://yandex.ru/dev/dictionary/doc/dg/reference/lookup.html - для определения типа слова
// @TODO Эмоциональная окраска слова
// @TODO Если база пустая, то генерация предложения уходит в бесконечный цикл