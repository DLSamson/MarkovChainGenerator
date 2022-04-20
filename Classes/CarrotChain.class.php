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
        return $this->unserialize($diff);
    }

    public static function serialize() : array {

    }

    public static function deserialize($data) : CarrotChain {

    }
}