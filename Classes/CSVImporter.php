<?php
namespace Classes;

use Classes\Database;
use Classes\Table;
use Exception;
use PDOException;

class CSVImporter
{
    private $pdo;
    private $logger;

    public function __construct()
    {
        $database = new Database();
        $this->pdo = $database->getConnection();
        $this->logger = new Logger('dev.log');
    }

    public function importCSV($csvFile, Table $importTable, $skipLines = 1, $batchSize = 100)
    {
        $handle = fopen($csvFile, 'r');

        if(!$handle) {
            $this->logger->logMessage("ERROR: Failed to open csv file");
            echo "failed to open csv";
            return;
        }

        if(!$importTable->tableExists()) {
            $tableCreated = $this->createImpTable($importTable);
          
            if($tableCreated)
                echo "sheet ids table created!";  
            
        } 
        /*else {
            echo "Already exists!";
        }*/

        // begin database transaction
        $this->pdo->beginTransaction();

        try {

            $this->skipLines($handle, $skipLines); // Skip lines
        
            // $lineCount = 0;
            $batch = [];
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $batch[] = $data;
                
                if (count($batch) >= $batchSize) {
                    $inserted = $this->insertDataInBatch($importTable, $batch);
                    // $lineCount += count($batch);
                    $batch = [];
                    
                }
            }
            
            if (!empty($batch)) {
                $this->insertDataInBatch($importTable, $batch);
                // $lineCount += count($batch);
            }
            
            $this->pdo->commit();
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->logger->logException($e);
        }
        
        fclose($handle);
        
        return true;

    }

    public function insertDataInBatch(Table $importTable, $batch)
    {
        $dataToInsert = [];
        foreach ($batch as $dataSet) {
            $existingSheetId = $importTable->findColumnByReference(['sheetId' => $dataSet[1]], 'sheetId');
            // print_r(var_dump($existingSheetId));
            if(!$existingSheetId)  {
                $dataToInsert[] = [
                    'name' => $dataSet[0],
                    'sheetId' => $dataSet[1],
                    'haveAccess' => strtolower($dataSet[2]) == 'yes',
                    'readSheets' => $this->convertToJSON($dataSet[3])
                ];
            }
            else {
                $this->logger->logMessage("{$dataSet[1]} already exists!");
                $importTable->updateRecord([
                    'name' => $dataSet[0],
                    'sheetId' => $dataSet[1],
                    'haveAccess' => strtolower($dataSet[2]) == 'yes',
                    'readSheets' => $this->convertToJSON($dataSet[3])
                ],['sheetId' => $existingSheetId]);
            }
        }
        
        if(count($dataToInsert)) {
            $importTable->insertRecords($dataToInsert);

            return true;
        }
        
        return false;
    }

    public function convertToJSON($sheets)
    {
        $sheets = explode(",", $sheets);

        foreach($sheets as &$sheet) {
            $sheet = trim($sheet);
        }

        return json_encode($sheets);
    }

    public function skipLines($handle, $count)
    {
        for ($i = 0; $i < $count; $i++) {
            if (fgetcsv($handle, 1000, ",") === false) {
                break;
            }
        }
    }

    public function createImpTable(Table $importTable)
    {
        return $importTable->createTable('id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), sheetId VARCHAR(255), haveAccess TINYINT(1), readSheets JSON');
    }
}

?>