<?php

use Medoo\Medoo;
require_once 'CarrotChain.class.php';

class TextGenerator
{
    public function __construct(ILog &$log, ICarrotDB &$database) {
        $this->log = $log;
        $this->database = $database;
        $this->log->write('TextGenerator object has been created');
    }

    private ILog $log;
    private ICarrotDB $database;

    public function generateSentence(array $params) : string {
        $wordsAmount = 0;
        $sentence = '';
        $currentWord = 'START';
        $buffer = array();

        while($currentWord != 'END') {
            $word = '';
            if(array_key_exists($currentWord, $buffer)) {
                $word = $this->getRandomWord($buffer[$currentWord]);
            } else {
                $words = $this->database->getNextWords($currentWord);
                $buffer[$currentWord] = $words;
                $word = $this->getRandomWord($words);
            }
            $sentence .= $word != 'END' ? ' '.$word : '';
            $wordsAmount += 1;
            $currentWord = $word;
            $this->log->write('Sentence: '.$sentence);
        }
        return $sentence;
    }

    /**
     * @param array $words
     * [word => amount]
     * @return string
     */
    private function getRandomWord(array $words) : string {
        $totalAmount = array_sum($words);
        $rand = rand(0, $totalAmount);
        $count = 0;

        foreach ($words as $word => $amount) {
            if($rand >= $count && $rand <= $amount+$count) {
                return $word;
            }
            else {
                $count += $amount;
            }
        }
        throw new Exception('Word not found');
    }
}