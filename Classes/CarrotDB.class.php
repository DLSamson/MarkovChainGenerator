<?php

require_once 'ICarrotDB.php';
use Medoo\Medoo;

class CarrotDB implements ICarrotDB
{
    public function __construct(Medoo $database)
    {
        $this->database = $database;
    }

    private Medoo $database;

    public function getData(): CarrotChain
    {
        // TODO: Implement getData() method.
    }

    public function setData(): void
    {
        // TODO: Implement setData() method.
    }

    public function checkData(): CarrotChain
    {
        // TODO: Implement checkData() method.
    }
}