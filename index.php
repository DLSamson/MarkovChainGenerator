<?php

/*This file need to check some specific data*/
error_reporting(E_ERROR);
//require_once 'Classes/TextGenerator.class.php';
//
require_once 'main.php';
//echo strlen(md5(123)).'<br>';
//echo strlen(md5(124));

/*
$json = file_get_contents('Data/exportFull0-500.json');
$getTexts = function($json) {
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

$data = $getTexts($json);
foreach ($data as $text) {
    echo '<p>'.$text . PHP_EOL.'</p>';
    echo '<p>'.TextGenerator::prettifyTexts($text) . PHP_EOL.'</p>';
}
