<?php

namespace Classes;

use Exception;

class Logger
{
    private $logFile;
    
    public function __construct($logFile) {
        $this->logFile = $logFile;
        
        // Set the default timezone to IST
        date_default_timezone_set('Asia/Kolkata');
    }
    
    public function logException(Exception $e) {
        $logMessage = '[' . date('Y-m-d H:i:s') . '] ERROR: ' . $e->getMessage() . PHP_EOL;
        
        $fileHandle = fopen($this->logFile, 'a');
        
        fwrite($fileHandle, $logMessage);
        
        fclose($fileHandle);
    }
    
    public function logMessage(string $message)
    {
        $logMessage = '[' . date('Y-m-d H:i:s') . '] MESSAGE: ' . $message . PHP_EOL;
        
        $fileHandle = fopen($this->logFile, 'a');
        
        fwrite($fileHandle, $logMessage);
        
        fclose($fileHandle);
    }
}

?>