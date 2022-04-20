<?php

/* @var Logger $log         */
/* @var TextGenerator $tg   */
/* @var Medoo $db           */
/* @var array $confg        */

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
$tg->collectData('Data/exportFull0-500.json', $collectData);