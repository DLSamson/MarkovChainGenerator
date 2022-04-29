<?php

require_once 'ICarrotDB.php';
use Medoo\Medoo;

class CarrotDB implements ICarrotDB
{
    public function __construct(Medoo $database, ILog &$log, string $table)
    {
        $this->database = $database;
        $this->log = &$log;
        $this->table = $table;
        $this->log->write('CarrotDB object has been created');
    }

    private Medoo $database;
    private ILog $log;
    private string $table;

    public function updateData(CarrotChain $chain) : void
    {
        foreach ($this->getStepUpdate($chain) as $item) {
            try {
                /* @TODO Fix error of buffering queries  PDOException: SQLSTATE[HY000]: General error: 2014*/
                $query = $this->database->exec($item);
                if(!isset($query)) {
                    $this->log->write($this->database->error);
                    $this->log->write($this->database->errorInfo);
                }
                else {
                    $query->fetchAll();
                }
            } catch (Exception $e) {
                $this->log->write($e);
            }

        }
    }

    /**
     * @param string $word
     * @return array
     * [word => amount]
     */
    public function getNextWords(string $word) : array {
        $result = array();
        $data = $this->database->select(
            $this->table,
            ['nextWord', 'amount'],
            [
                'word' => $word,
            ]
        );
        foreach ($data as $row) {
            $result[$row['nextWord']] = $row['amount'];
        }
        return $result;
    }
    public function getNextWord(string $word, $where) : string {
        $result = '';

        return $result;
    }

    private function getStepUpdate(CarrotChain $chain) : Generator {
        $array = $chain->serialize();
        $size = $chain->getCount();
        $step = $size > 1000 ? 1000 : $size;
        $pos = 0;

        do {
            $sql = [];
            for ($i = $pos; $i < $pos+$step; $i++) {
                $item = $array[$i];
                $sql[] = $this->prepareQuery($item);
            }
            $sql = implode(PHP_EOL, $sql);
            $pos += $step;

            /* @TODO Does not inclides last queries */

            $this->log->write("Updated ".$pos.'/'.$size);
            yield $sql;
        } while ($pos < $size);
    }
    private function prepareQuery($item) : string {
        $quotedItem = array();
        foreach ($item as $key => $value) {
            $quotedItem[$key] = $this->database->quote($value);
        }
        $item = $quotedItem;
        return "
            INSERT INTO wordstest
            (id, hash, word, nextWord, amount)
            VALUES (`id`, {$item['hash']}, {$item['word']}, {$item['nextWord']}, {$item['amount']})
            ON DUPLICATE KEY
            UPDATE `amount`=`amount`+{$item['amount']};
            ";
    }

}