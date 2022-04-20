<?php

use Medoo\Medoo;
require_once 'CarrotChain.class.php';

class TextGenerator
{
    public function __construct(ILog &$log, ICarrotDB &$database) {
        $this->log = $log;
        $this->db = $database;
        $this->log->write('TextGenerator object has been created');
    }

    private ILog $log;
    private ICarrotDB $db;

    /**
     * @param $jsonPath
     * @param callable $callback
     * gives json, must return array of texts
     */
    public function collectData($jsonPath, callable $callback) : void {
        $this->log->write('Reading JSON');
        $json = '';
        if(file_exists($jsonPath)) {
            $this->log->write('Found file...');
            $json = file_get_contents($jsonPath);
        }
        else {
            $this->log->write('File does not exist...', Logger::Error);
        }
        $this->log->write('Reading JSON completed');
        empty($json) ? $this->log->write('JSON is empty', Logger::Error) : $this->log->write('JSON length: '.strlen($json));
        $this->log->write('Parsing JSON through callback');
        $data = $callback($json);
        empty($data) ? $this->log->write('Texts are empty', Logger::Error) : $this->log->write('Got '. count($data) .' texts');
        $this->log->write('Starting collecting data...');
        $texts = array();
        $words = 0;
        foreach ($data as $key => $value) {
            $value = str_ireplace("\xc2\xa0", ' ', $value);
            $texts[$key] = explode(' ', $value);
            $words += count($texts[$key]);
        }
        $this->log->write('Got ' . $words . ' words...');

        $this->log->write('Creating word links...');
        $links = $this->makeWordLinks($texts);
        $this->log->write('Created word links');
        $this->log->write('Links has ' . count($links) . ' connections');

        $this->log->write('Making CarrotChain');
        $chain = $this->makeChainElements($links);

        $this->log->write('Saving links to DataBase...');
        $this->saveLinksToDB($chain);
        $this->log->write('Saving completed');
    }

    public function generateSentence() : string {
        $sentence = '';
        $currentWord = 'START';
        while($currentWord != 'END') {
             $result = $this->db->rand($this->dataTableName, 'nextWord',[
                'word' => $currentWord,
                'LIMIT' => 1,
            ])[0];
             if($result != 'END') {
                 $sentence .= $result . ' ';
             }
            $currentWord = $result;
        }

        return $sentence;
    }
    public static function prettifyTexts(string $text) : string {
        $result = preg_replace('/(\.|,)+/', '. ', $text);
        return $result == null ? die('Prettify texts error') : $result;
    }

    /**
     * @param array $links
     * Array of ChainElements
     */
    private function saveLinksToDB(CarrotChain $chain) : void {
        $this->log->write('Checking out what we already have...');
        $checkout = array();
        foreach ($chain->getArray() as $element) {
            $checkout['OR']['hash #'.$element->word.$element->nextWord] = $element->hash;
        }

        $this->log->write('Making request to database');
        $result = $this->db->select($this->dataTableName, ['hash'], $checkout);
        empty($result) ? $this->log->write('No rows found') : $this->log->write('Found '. count($result).' rows');
        foreach ($result as $row) {
            $this->log->write('Found row - '.$row['hash']);
        }

        $this->log->write('Preparing data...');
        $newResult = array();
        foreach ($result as $row) {
            $newResult[] = $row['hash'];
        }
        $result = $newResult;

        $insert = $chain->getDiffHash($result);
        $update = $chain->getDiff($insert);

        $this->log->write('Total        - '.$chain->getCount());
        $this->log->write('Total insert - '.$insert->getCount());
        $this->log->write('Total update - '.$update->getCount());

        $this->log->write('Making requests to database...');
        $this->insertData($this->dataTableName, $insert->serializeArray());
        $this->updateData($this->dataTableName, $update->serializeArray());

        $this->log->write('Saving complete');
    }

    private function updateData(string $table, array $data) {
        if(!count($data)) {
            return;
        }

        $this->db->action(function (Medoo $db) use ($table, &$data) {
            foreach ($data as $item) {
                $db->update($table,
                    [ 'amount[+]'=> $item['amount']],
                    [
                        'AND' => [
                            'hash' => $item['hash'],
                        ]
                    ]
                );
            }
        });
    }
    private function insertData(string $table, array $data) {
        if(!count($data)) {
            return;
        }

        $this->db->action(function (Medoo $db) use ($table, &$data) {
            foreach ($data as $item) {
                $db->insert($table,
                    [
                        'hash'=>$item['hash'],
                        'word'=> $item['word'],
                        'nextWord'=>$item['nextWord'],
                        'amount'=>$item['amount'],
                    ]
                );
            }
        });
    }

    /**
     * @param $links
     * Word => (nextWord => amount)
     * @return CarrotChain
     */
    private function makeChainElements(array $links) : CarrotChain {
        $chain = new CarrotChain();
        foreach ($links as $word => $data) {
            foreach ($data as $nextWord => $amount) {
                $chain->addElement(new ChainElement(ChainElement::generateHash($word, $nextWord), $word, $nextWord, $amount));
            }
        }
        return $chain;
    }
    private function makeWordLinks(array $explodedTexts) : array {
        $links = array('START' => [], 'END' => []);
        foreach($explodedTexts as $text) {
            $textSize = count($text);
            for($i = 0; $i < $textSize; $i++) {
                if ($i == 0) {
                    if(in_array($text[$i], $links['START'])) {
                        $links['START'][$text[$i]] += 1;
                    }
                    else {
                        $links['START'][$text[$i]] = 1;
                    }
                }
                if ($i == $textSize - 1) {
                    if(in_array(['END'], $links[$text[$i]])) {
                        $links[$text[$i]]['END'] += 1;
                    }
                    else {
                        $links[$text[$i]]['END'] = 1;
                    }
                }
                if($i != $textSize - 1 && $i != 0) {
                    if(in_array($text[$i+1], $links[$text[$i]])) {
                        $links[$text[$i]][$text[$i+1]] += 1;
                    }
                    else {
                        $links[$text[$i]][$text[$i+1]] = 1;
                    }
                }
            }
        }
        return $links;
    }
}