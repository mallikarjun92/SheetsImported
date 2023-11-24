<?php
require_once 'autoload.php';

use Classes\CSVImporter;
use Classes\Table;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import-csv'])) {
    $csvFile = $_FILES["import-csv"]["tmp_name"];
    $csvImporter = new CSVImporter();

    $importTable = new Table(Table::SHEET_IDS);

    $imported = $csvImporter->importCSV($csvFile, $importTable);

    if($imported)
        echo "Imported sheet ids!";
    else
        echo "Import failed!";
    
}
else {
    echo "No file or something went wrong!";
}

?>