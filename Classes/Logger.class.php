<?php

require_once 'ILog.php';

class Logger implements ILog
{
    public function __construct(string $logPath, $isDebug) {
        $this->logDirectory = $logPath;
        $this->isDebug = $isDebug;
        $this->stream = fopen($logPath.'/log-'.$this->getTimeFile().'.txt', 'a');
        $this->write('Log object has been created');
    }
    public function __destruct() {
        fclose($this->stream);
    }

    private $stream;
    private bool $isDebug;
    private $startTime;

    public const Error = 0;
    public const Warning = 1;
    public const Normal = 2;
    public const Strong = 3;

    public function write($data, $status = Logger::Normal) : void {
        if($this->isDebug || $status == Logger::Strong) {
            $message = $this->getType($status).$this->getTime().$data.PHP_EOL;
            fwrite($this->stream, $message);
            print($message);
        }
        if($status == Logger::Error) {
            die();
        }
    }
    public function startTime() {
        $time = time();
        $this->startTime = $time;
    }
    public function endTime() {
        $currentTime = time();
        $difference = $currentTime - $this->startTime;
        $this->write('Execution took: '. $difference.'s');
    }

    private function getTime() : string {
        $timeFormat = '[H:i:s - d.m.y]: ';
        $date = date($timeFormat, time());
        return $date;
    }
    private function getTimeFile() : string {
        $timeFormat = 'd_m_y';
        $date = date($timeFormat, time());
        return $date;
    }

    private function getType($status) : string {
        $type = '';
        switch ($status) {
            case Logger::Error: {
                $type = ' Error ';
                break;
            }
            case Logger::Warning: {
                $type = 'Warning';
                break;
            }
            case Logger::Normal: {
                $type = '  Log  ';
                break;
            }
            case Logger::Strong: {
                $type = '_Strong';
                break;
            }
        }
        return '['.$type.']:';
    }
}