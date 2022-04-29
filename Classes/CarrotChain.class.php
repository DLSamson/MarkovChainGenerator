<?php

require 'ChainElement.class.php';

class CarrotChain
{
    public function __construct()
    {
        $this->elements = array();
    }
    private array $elements;

    public function addElement(ChainElement $element) {
        $this->elements[] = $element;
    }

    public function getCount() : int {
        return count($this->elements);
    }
    public function getDiff(CarrotChain $chain) : CarrotChain {
        $me = $this->serialize();
        $chain = $chain->serialize();
        $diff = array_diff_assoc($me, $chain);
        return $this->deserialize($diff);
    }

    public function serialize() : array {
        $array = array();
        foreach($this->elements as $element) {
            $array[] = [
                'word' => $element->word,
                'nextWord' => $element->nextWord,
                'amount' => $element->amount,
                'hash' => empty($element->hash) ? ChainElement::generateHash($element->word, $element->nextWord) : $element->hash,
            ];
        }
        return $array;
    }
    public static function deserialize(array $data) : CarrotChain {
        $chain = new CarrotChain();
        foreach($data as $element) {
            $chain->addElement(
                new ChainElement(
                    $element['word'],
                    $element['nextWord'],
                    $element['amount']
                )
            );
        }
        return $chain;
    }
}