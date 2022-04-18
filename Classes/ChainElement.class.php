<?php

class ChainElement
{
    public function __construct($word, $nextWord, $amount)
    {
        $this->word = $word;
        $this->nextWord = $nextWord;
        $this->amount = $amount;
    }

    public string $word;
    public string $nextWord;
    public int $amount;

    public function __toString(): string
    {
        return $this->word.'=>'.$this->nextWord;
    }
}