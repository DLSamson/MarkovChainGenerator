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
    public function getElementWhere(string $where, $cond) {
        return array_filter(
            $this->elements,
            function ($element) {
                global $where;
                global $cond;
                return $element->$where == $cond;
            }
        );
    }
    public function getCount() : int {
        return count($this->elements);
    }

    public function getDiff(CarrotChain $chain) {
        $me = $this->serializeArray();
        $chain = $chain->serializeArray();
        $diff = array_diff_assoc($me, $chain);
        return $this->deserializeArray($diff);
    }

    public function serializeArray() : array {
        $array = array();
        foreach($this->elements as $item) {
            $array[] = [
                'word' => $item->word,
                'nextWord' => $item->nextWord,
                'amount' => $item->amount,
            ];
        }
        return $array;
    }

    public function deserializeArray(array $array) : CarrotChain {
        $chain  = new CarrotChain();
        foreach ($array as $element) {
            $chain->addElement(new ChainElement($element['word'], $element['nextWord'], $element['amount']));
        }
        return $chain;
    }
    public function getArray() : array {
        return $this->elements;
    }
}