<?php

interface ICarrotDB {
    public function updateData(CarrotChain $chain) : void;
    public function getNextWords(string $word) : array;
    public function getNextWord(string $word, $where) : string;
}