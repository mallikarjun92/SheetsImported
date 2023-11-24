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
// $spreadsheetId = '1q_Jk21cLmgZ4MkSYgporQHqcdBpCs44OivxjaFKwgqg';

$spreadsheetIdsTable = new Table(Table::SHEET_IDS);
$spreadSheetLinks = $spreadsheetIdsTable->selectAllRecords();

// $spreadsheetId = '1dBDhm0tHdDC_H8Dw-S0kA3MfgxvTttpRU8DIF_ET7qw';
$range = 'Sheet1!A1:C10'; // adjust to your sheet name and range

$table = new Table(Table::IMPORTED_SPREADSHEETS);

if(!$table->tableExists()) {
    $created = $table->createTable('id INT AUTO_INCREMENT PRIMARY KEY, date DATE, source VARCHAR(255), counsellor VARCHAR(255), name VARCHAR(255), mobile_no VARCHAR(255), location VARCHAR(255), course VARCHAR(255), call_status VARCHAR(255), comments VARCHAR(255), created DATETIME DEFAULT CURRENT_TIMESTAMP, updated DATETIME DEFAULT CURRENT_TIMESTAMP');
    
    if($created)
        echo "created table";
    else 
        echo "Table exists already!";
}

if(count($spreadSheetLinks)) {
    foreach ($spreadSheetLinks as $spreadSheetLink) {
        $spreadsheetId = null;
        $skipSheets = null;
        if((bool)$spreadSheetLink['haveAccess']){
            $spreadsheetId = $spreadSheetLink['sheetId'];
            $skipSheets = json_decode($spreadSheetLink['skipSheets']);
    
            echo "skip sheets \n";
            print_r($skipSheets);
            if($spreadsheetId) {
                $sheets = @$service->spreadsheets->get($spreadsheetId);
            }
    
            if(count($sheets)) {
                importSpreadSheetData($sheets, $service, $spreadsheetId, $table, $skipSheets);
            }
        }
    }
} else {
    echo "0 sheet_ids";
}



function importSpreadSheetData($sheets, $service, $spreadsheetId, $table, $skipSheets)
{
    foreach ($sheets as $sheet) {
        if(in_array($sheet->getProperties()->getTitle(), $skipSheets)) {
            continue;
        }

        // echo 'Sheet Title: ' . $sheet->getProperties()->getTitle() . "\n";
        // echo 'Sheet ID: ' . $sheet->getProperties()->getSheetId() . "\n";
        // echo 'Grid Properties: ' . json_encode($sheet->getProperties()->getGridProperties()) . "\n";
        // Add more information as needed
        // echo "----------------------\n";
        
        $range = "{$sheet->getProperties()->getTitle()}!A2:I";
        
        $response = @$service->spreadsheets_values->get($spreadsheetId, $range);
        
        $values = $response->getValues();

        if(count($values)) {
            $chunks = array_chunk($values, 100);

            $rowCount = 0;
            $count = 0;
            foreach($chunks as $chunk) {
                $data = [];
                if(count($chunk)) {
                    foreach($chunk as $row) {
                        $rowCount += 1;
                        $date = DateTime::createFromFormat('d-M-Y', $row[0]);
                        if($date != false) {
                            $dateStr = $date->format('Y-m-d');
                            $data[] = [
                                'date' => $dateStr,
                                'source' => $row[1],
                                'counsellor' => $row[2],
                                'name' => $row[3],
                                'mobile_no' => $row[4],
                                'location' => $row[5],
                                'course' => $row[6],
                                'call_status' => $row[7],
                                'comments' => $row[8],
                            ];
                        } else {
                            print_r("Invalid date format for value: " . var_dump($row[0]) . " at row {$rowCount} \n");
                        }
                    }
                    
                    if(count($data)) {
                        $inserted = $table->insertRecords($data);
                        
                        if($inserted)
                            $count += count($data);
                    }
                }
            }

            echo "Imported $count rows!";
        }
        
    }
}


?>