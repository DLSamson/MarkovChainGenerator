<?php

use Medoo\Medoo;

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
        $links = @$this->makeWordLinks($texts);
        $this->log->write('Created word links');
        $this->log->write('Links has ' . count($links) . ' connections');

        $this->log->write('Saving links to DataBase...');
        $this->saveLinksToDB($links);
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

    //word = nextWord => amount;
    private function saveLinksToDB(array $links) : void {
        //Формируем список                  DONE
        //Проверяем, что есть из списка     DONE
        //Обновляем
        //Добавляем
        $checkData = array();
        $checkData['OR'] = array();
        $this->log->write('Making data to check');
        foreach ($links as $word => $data) {
            foreach ($data as $nextWord => $amount) {
                $checkData['OR']['AND #'.$word.$nextWord] = [
                    'word' => $word,
                    'nextWord' => $nextWord,
                ];

            }
        }
        $this->log->write('Making request to DATABASE');
        $result = $this->db->select($this->dataTableName, ['word','nextWord'], $checkData);

        $this->log->write('Found rows: '.count($result));
        foreach ($result as $row) {
            $this->log->write('Found row: '.$row['word'].' - '.$row['nextWord']);
        }

        $this->log->write('Making add and update lists...');
        $checkData = array_values($checkData['OR']);
        $toAdd = @array_diff_assoc($checkData, $result);
        $toUpdate = @array_diff_assoc($checkData, $toAdd);

        $this->log->write('Total rows    ' . count($checkData));
        $this->log->write('Total adds    ' . count($toAdd));
        $this->log->write('Total updates ' . count($toUpdate));

        $this->log->write('Preparing WHERE for updates');
        $toUpdate_cond = array();
        $toUpdate_cond['OR'] = array();
        //@$links[$item['word']][$item['nextWord']]

        foreach($toUpdate as $key => $item) {
            $toUpdate[$key]['amount[+]'] = @$links[$item['word']][$item['nextWord']];
            $toUpdate_cond['OR']['AND #'.$item['word'].$item['nextWord']] = [
                'word' => $item['word'],
                'nextWord' => $item['nextWord'],
            ];
        }

//        $this->log->write('Inserting new values');
//        $this->db->insert($this->dataTableName, $toAdd);
//
        $this->log->write('Updating old values');
        $data = $this->db->update($this->dataTableName, $toUpdate, $toUpdate_cond);
        $this->log->write('Affected '.$data->rowCount(). ' rows');
        $this->log->write($this->db->last());
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