<?php

/* @var Logger $log         */
/* @var TextGenerator $tg   */
/* @var array $confg        */
/* @var MarkovChain $markovChain */

$collectData = function ($json) {
    $data = json_decode($json, true);
    $texts= array();
    foreach ($data as $value) {
        $texts[] = $value['text'];
    }
    return $texts;
};

$markovChain->collectData('Data/data.json', $collectData);