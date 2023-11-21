<?php

require_once __DIR__ . '/vendor/autoload.php';

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
$spreadsheetId = '1dBDhm0tHdDC_H8Dw-S0kA3MfgxvTttpRU8DIF_ET7qw';
$range = 'Sheet1!A1:C10'; // adjust to your sheet name and range

// Fetch values from the spreadsheet
// $response = @$service->spreadsheets_values->get($spreadsheetId, $range);
// $values = $response;

// print_r(($values->getValues()));

$sheets = @$service->spreadsheets->get($spreadsheetId);

print_r(count($sheets));

foreach ($sheets as $sheet) {
    echo 'Sheet Title: ' . $sheet->getProperties()->getTitle() . "\n";
    echo 'Sheet ID: ' . $sheet->getProperties()->getSheetId() . "\n";
    echo 'Grid Properties: ' . json_encode($sheet->getProperties()->getGridProperties()) . "\n";
    // Add more information as needed
    echo "----------------------\n";
}


?>