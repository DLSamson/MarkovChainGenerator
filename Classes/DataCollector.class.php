<?php

class DataCollector {
    public function __construct(ILog &$log, ICarrotDB &$database)
    {
        $this->log = &$log;
        $this->database = &$database;
        $this->log->write('DataCollector object has been created');
    }

    private ILog $log;
    private ICarrotDB $database;

    public function collectData(string $path, callable $callback) : void {
        $this->log->write('Starting collecting data');
        $data = $this->getTexts($path, $callback);

        //Get array of exploded sentences
        $this->log->write('Exploding texts');
        $data = $this->explodeTexts($data);

        $this->log->write('Making links');
        $data = @$this->makeLinks($data);

        $this->log->write('Making chains');
        $data = $this->makeChains($data);
        $this->log->write('Total rows: '. $data->getCount());

        $this->log->getMemoryUsage();
        $this->database->updateData($data);

        $this->log->write('Data has been collected');
        $this->log->getMemoryUsage();
        $this->log->passedTime();
    }

    private function getTexts(string $path, callable $callback) : array {
        $this->log->write('Reading JSON');
        $json = file_get_contents($path);
        empty($json) ? die('JSON is empty') : $this->log->write('JSON length: '. strlen($json));;
        //Get texts
        $data = $callback($json);
        $this->log->write('Texts count: '. count($data));
        return $data;
    }
    private function explodeTexts(array $texts, int &$counter = null) : array {
        $texts = $this->prettifyTexts($texts);

        $result = array();
        foreach ($texts as $text) {
            $sentences = explode('. ', $text);
            foreach ($sentences as $sentence) {
                $words = explode(' ', $sentence);
                foreach ($words as &$word) {
                    $word = $this->prettifyWord($word);
                }
                unset($word);
                $result[] = $words;
            }

        }
        $this->log->write('Sentences count: '. count($result));
        return $result;
    }
    private function prettifyTexts(array $texts) : array {
        //Do whatever you need
        foreach ($texts as &$text) {
            $text = preg_replace('/(\.)+/', '. ', $text);
            $text = preg_replace('/(,)+/', ', ', $text);
            $text = preg_replace('/(\s)+/', ' ', $text);
            $text = str_replace("\xc2\xa0",' ', $text);

            //...
        }
        unset($text);
        return $texts;
    }
    private function prettifyWord(string $word) : string {
        //Do whatever you need
        $word = htmlspecialchars($word);
        $word = mb_strtolower($word);
        $word = preg_replace('/\s+/', ' ', $word);
        $word = preg_replace('/(\`|\')+/', '"', $word);
        $word = trim($word);
        /*
         * number+letters and vice versa
         * !letter
         * (c
         * )
         * &nbsp
         * */
//        $word = preg_replace('//', ' ', $word);
        //...
        return $word;
    }
    private function makeLinks(array $explodedTexts) : array {
        $links = [];
        foreach ($explodedTexts as $sentence) {
            $sentenceSize = count($sentence);
            for ($i = 0; $i < $sentenceSize; $i++) {
                /* @TODO make amount counter with if...else statement #$links['START'][$sentence[$i]] += 1; */
                if($i == 0) {
                    if(array_key_exists($sentence[$i], $links['START'])){
                        $links['START'][$sentence[$i]] += 1;
                    }
                    else {
                        $links['START'][$sentence[$i]] = 1;
                    }
                    //If word is the only one, It can be used at the beginning and at the end as well.
                    if(count($sentence) == 1) {
                        $links[$sentence[$i]]['END'] += 1;
                    }
                }

                if($i == $sentenceSize-1) {
                    if(array_key_exists(['END'], $links[$sentence[$i]])){
                        $links[$sentence[$i]]['END'] += 1;
                    }
                    else {
                        $links[$sentence[$i]]['END'] = 1;
                    }
                }

                if($i != $sentenceSize-1) {
                    if(array_key_exists($sentence[$i+1], $links[$sentence[$i]])){
                        $links[$sentence[$i]][$sentence[$i+1]] += 1;
                    }
                    else {
                        $links[$sentence[$i]][$sentence[$i+1]] = 1;
                    }
                }
            }
        }
        $this->log->write('Links count: '. count($links));
        return $links;
    }
    private function makeChains(array $links) : CarrotChain {
        $chain = new CarrotChain();
        foreach ($links as $word => $data) {
            foreach ($data as $nextWord => $amount) {
                $chain->addElement(
                    new ChainElement(
                        $word,
                        $nextWord,
                        $amount
                    )
                );
            }
        }
        return $chain;
    }
    private function replaceAmounts(CarrotChain $stack, CarrotChain $replace) : CarrotChain {
        $result = new CarrotChain();
        foreach($stack->serialize() as $element) {
            foreach ($replace->serialize() as $find) {
                if($element['hash'] == $find['hash']) {
                    $element['amount'] = $find['amount'];
                    $result->addElement(
                        new ChainElement(
                            $element['word'],
                            $element['nextWord'],
                            $element['amount'],
                        )
                    );
                }
            }
        }
        return $result;
    }
}