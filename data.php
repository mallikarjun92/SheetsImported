<?php

require_once 'autoload.php';

use Classes\Table;
use Classes\Logger;

$importedSpreadSheet = new Table(Table::IMPORTED_SPREADSHEETS);

if(isset($_GET)) {
    if($_GET['query'] == 'all') {
        allCallData($importedSpreadSheet);
    }

    if($_GET['query'] == 'RNR') {
        allRNR($importedSpreadSheet);
    }
}
else {
    print_r('not set');
}


function allCallData(Table $importedSpreadSheet) {
    $callStatData = $importedSpreadSheet->selectByCustomQuery("SELECT call_status, COUNT(call_status) as count FROM {$importedSpreadSheet->getTableName()} GROUP BY call_status");
    
    echo json_encode($callStatData);
    return;
}

function allRNR(Table $importedSpreadSheet) {
    $callStatData = $importedSpreadSheet->selectByCustomQuery("SELECT * FROM {$importedSpreadSheet->getTableName()} WHERE call_status = 'RNR'");
    
    echo json_encode($callStatData);
    return;
}


?>