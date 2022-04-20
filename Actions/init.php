<?php


require_once 'Classes/Logger.class.php';
require_once 'Classes/CarrotDB.class.php';
require_once 'Classes/DataCollector.class.php';
require_once 'Classes/TextGenerator.class.php';
require_once 'Classes/MarkovChain.class.php';

use Medoo\Medoo;

$log = new Logger('./Logs/', DEBUG_MODE);
$config = require_once 'config.php';
$database = new CarrotDB(new Medoo($config['database']));
$dataCollector = new DataCollector($log, $database);
$textGenerator = new TextGenerator($log,$database);

$markovChain = new MarkovChain($database, $dataCollector, $textGenerator);