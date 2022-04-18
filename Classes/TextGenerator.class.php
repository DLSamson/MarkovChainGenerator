<?php

use Medoo\Medoo;
require_once 'CarrotChain.class.php';

class TextGenerator
{
    public function __construct(ILog $log, Medoo $database, string $tableName) {
        $this->log = $log;
        $this->db = $database;
        $this->dataTableName = $tableName;
        $this->log->write('TextGenerator object has been created');
    }

    private ILog $log;
    private Medoo $db;
    private string $dataTableName;

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

    /**
     * @param array $links
     * Array of ChainElements
     */
    private function saveLinksToDB(CarrotChain $chain) : void {
        //Формируем список                  DONE
        //Проверяем, что есть из списка     DONE
        //Обновляем
        //Добавляем
        $checkData = array();
        $checkData['OR'] = array();

        $this->log->write('Making data to check');
        foreach($chain->getArray() as $element) {
            $checkData['OR']['AND #'.$element->word.$element->nextWord] = [
                'word' => $element->word,
                'nextWord' => $element->nextWord,
            ];
        }

        $this->log->write('Making request to DATABASE');
        $result = $this->db->select($this->dataTableName, ['word','nextWord', 'amount'], $checkData);

        $this->log->write('Found rows: '.count($result));
        foreach ($result as $row) {
            $this->log->write('Found row: '.$row['word'].' - '.$row['nextWord']);
        }

        $this->log->write('Making add and update lists...');
        $result = $chain->deserializeArray($result);
        $toAdd = $chain->getDiff($result);
        $toUpdate = $chain->getDiff($toAdd);

        $this->log->write('Total rows    ' . $chain->getCount());
        $this->log->write('Total adds    ' . $toAdd->getCount());
        $this->log->write('Total updates ' . $toUpdate->getCount());

        $this->log->write('Starting updating values');

        $this->log->write('Inserting new values');
        if($toAdd->getCount()) {
            $this->db->insert($this->dataTableName, $toAdd->serializeArray());
        }


        $this->log->write('Updating old values');
        if($toUpdate->getCount()) {
            $data = $this->makeMassiveUpdate($toUpdate);
        }

        $data instanceof PDOStatement ? $this->log->write('Updating completed') : $this->log->write('Updating is not completed', Logger::Error);

        $this->log->write('Collecting data completed');
    }

    /**
     * @param $links
     * Word => (nextWord => amount)
     * @return CarrotChain
     */
    private function makeChainElements($links) : CarrotChain {
        $chain = new CarrotChain();
        foreach ($links as $word => $data) {
            foreach ($data as $nextWord => $amount) {
                $chain->addElement(new ChainElement($word, $nextWord, $amount));
            }
        }
        return $chain;
    }
    private function makeMassiveUpdate(CarrotChain $update) {
        $this->log->write("Making query...");
        $query = array();
        foreach ($update->getArray() as $item) {
            $query[] = "UPDATE {$this->dataTableName} SET amount=amount+1 WHERE (word='{$item->word}' AND nextWord='{$item->nextWord}');";
        }
        $query = implode(' ', $query);

        $this->log->write("Executing query...");
        //$this->log->write('Query - '. $query);
        $data = $this->db->query($query);
        empty($data) ? $this->log->write('Problem has accured while executing', Logger::Error) : $this->log->write('Executing completed');
        return $data;
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
                    if(in_array('END', $links[$text[$i]])) {
                        $links[$text[$i]]['END'] += 1;
                    }
                    else {
                        $links[$text[$i]]['END'] = 1;
                    }
                }
                if($i != $textSize - 1) {
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