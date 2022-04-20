<?php

require_once 'ICarrotDB.php';
use Medoo\Medoo;

class CarrotDB implements ICarrotDB
{
    public function __construct(Medoo $database, ILog &$log)
    {
        $this->database = $database;
        $this->log = &$log;
        $this->log->write('CarrotDB object has been created');
    }

    private Medoo $database;
    private ILog $log;

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