<?php

class ChainElement
{
    public function __construct($hash, $word, $nextWord, $amount)
    {
        $this->hash = $hash;
        $this->word = $word;
        $this->nextWord = $nextWord;
        $this->amount = $amount;
    }

    public string $hash;
    public string $word;
    public string $nextWord;
    public int $amount;

    public function __toString(): string
    {
        return $this->word.'=>'.$this->nextWord;
    }
    public static function generateHash(string $word, string $nextWord) : string {
        return md5($word.$nextWord);
    }
}