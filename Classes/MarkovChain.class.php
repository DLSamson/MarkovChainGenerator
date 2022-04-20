<?php

class MarkovChain
{
    public function __construct(ICarrotDB &$database, DataCollector &$dataCollector, TextGenerator &$textGenerator)
    {
        $this->database = &$database;
        $this->dataCollector = &$dataCollector;
        $this->textGenerator = &$textGenerator;
    }

    private ICarrotDB $database;
    private DataCollector $dataCollector;
    private TextGenerator $textGenerator;

    public function collectData(string $path, callable $callback) : void {
        $this->dataCollector->collectData($path, $callback);
    }
}