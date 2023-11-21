<?php

require_once 'autoload.php';
// print_r('5');exit;

use Classes\Database;
use Classes\Table;
use Classes\Logger;
// use Google_Client;
// use Google_Service_Sheets;

// Set up Google Sheets API client
$client = @new Google_Client();
$client->setApplicationName('Import Sheet');
$client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
$client->setAuthConfig('service-acc-key.json');
$client->setAccessType('offline');

// Create Google Sheets service
$service = @new Google_Service_Sheets($client);

// Specify the spreadsheet ID and range
$spreadsheetId = '1q_Jk21cLmgZ4MkSYgporQHqcdBpCs44OivxjaFKwgqg';
// $spreadsheetId = '1dBDhm0tHdDC_H8Dw-S0kA3MfgxvTttpRU8DIF_ET7qw';
$range = 'Sheet1!A1:C10'; // adjust to your sheet name and range

// Fetch values from the spreadsheet
// $response = @$service->spreadsheets_values->get($spreadsheetId, $range);
// $values = $response;

// print_r(($values->getValues()));

$table = new Table(Table::IMPORTED_CSV_TABLE);

// print_r(var_dump(!$table->tableExists()));exit;
if(!$table->tableExists()) {
    $created = $table->createTable('id INT AUTO_INCREMENT PRIMARY KEY, status INT, task VARCHAR(255), date VARCHAR(255)');
    
    if($created)
        echo "created table";
    else 
        echo "Table exists already!";
}

$sheets = @$service->spreadsheets->get($spreadsheetId);

// print_r(count($sheets));

foreach ($sheets as $sheet) {
    // echo 'Sheet Title: ' . $sheet->getProperties()->getTitle() . "\n";
    // echo 'Sheet ID: ' . $sheet->getProperties()->getSheetId() . "\n";
    // echo 'Grid Properties: ' . json_encode($sheet->getProperties()->getGridProperties()) . "\n";
    // // Add more information as needed
    // echo "----------------------\n";
    
    $range = "{$sheet->getProperties()->getTitle()}!A1:C3";
    
    $response = @$service->spreadsheets_values->get($spreadsheetId, $range);
    
    $values = $response->getValues();
    
    $data = [];
    if(count($values)) {
        foreach($values as $val) {
            $data[] = ['status' => (bool)$val[0], 'task' => $val[1], 'date' => $val[2]];
        }
        
        $count = 0;
        if(count($data)) {
            $count = count($data);
            $inserted = $table->insertRecords($data);
            
            if($inserted) {
                echo "Imported $count rows from google sheet!";
            } else {
                echo "Something went wrong see dev.log";
            }
        }
    }
    
}


?>