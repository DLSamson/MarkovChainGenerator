<?php

class ChainElement
{
    public function __construct($word, $nextWord, $amount)
    {
        $this->word = $word;
        $this->nextWord = $nextWord;
        $this->amount = $amount;
        $this->hash = ChainElement::generateHash($word, $nextWord);
    }

    public string $word;
    public string $nextWord;
    public int $amount;
    public string $hash;

    public function __toString(): string
    {
        return $this->word.'=>'.$this->nextWord;
    }
    public static function generateHash(string $word, string $nextWord) : string {
        return md5($word.$nextWord);
    }
}