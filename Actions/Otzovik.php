<?php

/* @var Logger $log         */
/* @var TextGenerator $tg   */
/* @var array $confg        */
/* @var MarkovChain $markovChain */

$collectData = function($json) {
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

//$markovChain->collectData('Data/exportFull1500-2000.json', $collectData);
$log->write('Result: '. $markovChain->generateSentence([
    'minLength' => 40,
]));