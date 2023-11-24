<?php

namespace Classes;

use PDO;
use PDOException;
use Classes\Database;
use Classes\Logger;

class Table
{
    private $pdo;
    private $logger;
    private $tableName;
    
    public function __construct($tableName) {
        $database = new Database();
        $this->pdo = $database->getConnection();
        $this->tableName = $tableName;
        $this->logger = new Logger('dev.log');
    }
    
    // Table names stored as constants
    const IMPORTED_SPREADSHEETS = 'imported_spreadsheets';
    const SHEET_IDS = 'sheet_ids';
    
    public function getTableName()
    {
        return $this->tableName;
    }
    
    // Check if the specified table exists
    public function tableExists() {
        $sql = "SHOW TABLES LIKE :table_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':table_name', $this->tableName, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    // Create the specified table if it does not exist
    public function createTable($tableDefinition) {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tableName} ($tableDefinition)";
        
        try {
            $this->pdo->exec($sql);
            return true;
        } catch (PDOException $e) {
            $this->logger->logException($e);
            return false;
        }
    }
    
    // Insert a new record into the specified table
    public function insertRecord($data) {
        // $data is an associative array of column => value pairs
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->tableName} ($columns) VALUES ($placeholders)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            
            return $this->pdo->lastInsertId();
//             return true;
        } catch (PDOException $e) {
            $this->logger->logException($e);
            $this->pdo->rollBack();
            return false;
        }
    }
    
    public function insertRecords($data) {
        // $data is an array of associative arrays, where each inner array represents a record with column => value pairs
        if (empty($data)) {
            return false; // No data to insert
        }
        
        $columns = implode(', ', array_keys($data[0])); // Get column names from the first record
        $placeholders = ':' . implode(', :', array_keys($data[0])); // Create placeholders for the first record
        
        $sql = "INSERT INTO {$this->tableName} ($columns) VALUES ($placeholders)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            
            // Insert records in batches
            foreach ($data as $record) {
                $stmt->execute($record);
            }
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->logException($e);
//             $this->pdo->rollBack();
            return false;
        }
    }
    
    
    // exec customQuery
    public function customQuery($sql)
    {
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            $this->logger->logException($e);
            return false;
        }
    }
    
    // Select all records from the specified table
    public function selectAllRecords() {
        $sql = "SELECT * FROM {$this->tableName}";
        
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->logException($e);
            return false;
        }
    }
    
    // Select a record from table with a WHERE condition
    public function selectRecordWhere($conditions) {
        $whereConditions = array();
        
        foreach ($conditions as $column => $value) {
            // Ensure that the column name is safe to use
            $safeColumnName = $this->pdo->quote($column);
            $safeColumnName = trim($safeColumnName, "'");
            $whereConditions[] = "$safeColumnName = '$value'";
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        $sql = "SELECT * FROM {$this->tableName} WHERE $whereClause";
        
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->logException($e);
            return false;
        }
    }
    
    // Select all records from table with a WHERE condition
    public function selectRecordsWhere($conditions) {
        $whereConditions = array();
        
        foreach ($conditions as $column => $value) {
            // Ensure that the column name is safe to use
            $safeColumnName = $this->pdo->quote($column);
            $safeColumnName = trim($safeColumnName, "'");
            $whereConditions[] = "$safeColumnName = '$value'";
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        $sql = "SELECT * FROM {$this->tableName} WHERE $whereClause";
        
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->logException($e);
            return false;
        }
    }
    
    // Execute custom query by passing your sql query to this function
    public function selectByCustomQuery($sql)
    {
//         print_r($sql);exit;
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->logException($e);
            return false;
        }
    }
    
    // get distinct values from column
    public function selectDistinctColumn($column)
    {
        $sql = "SELECT DISTINCT $column FROM {$this->tableName}";
        
        return $this->selectByCustomQuery($sql);
    }
    
    // select column by id
    public function selectColumnById($column, $id)
    {
        // validate and sanitize
        $safeColumn = $this->pdo->quote($column);
        $safeColumn = trim($safeColumn, "'");
        $safeId = $this->pdo->quote($id);
        $safeId = trim($safeId, "'");
        
        $sql = "SELECT $safeColumn FROM {$this->tableName} WHERE id = $safeId";
        
        try {
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result !== false) {
                return $result[$column]; // Return the value
            } else {
                return null; // no result
            }
        } catch (PDOException $e) {
            $this->logger->logException($e);
            return false;
        }
    }
    
    public function findColumnByReference($reference, $targetColumn)
    {
        if (!is_array($reference) || empty($reference)) {
            return false;
        }
        
        $whereConditions = array();
        
        foreach ($reference as $column => $value) {
            // Validate and sanitize input 
            $safeColumn = $this->pdo->quote($column);
            $safeColumn = trim($safeColumn, "'");
            $safeValue = $this->pdo->quote($value);
            $safeValue = trim($safeValue, "'");
            $whereConditions[] = "$safeColumn = '$safeValue'";
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        $safeTargetColumn = $this->pdo->quote($targetColumn);
        $safeTargetColumn = trim($safeTargetColumn, "'");
        
        $sql = "SELECT $safeTargetColumn FROM {$this->tableName} WHERE $whereClause";
        
        try {
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result !== false) {
                return $result[$targetColumn]; // Return the value of the target column
            } else {
                return null; // no result
            }
        } catch (PDOException $e) {
            $this->logger->logException($e);
            return false;
        }
    }
    
    public function createIndexIfNotExists($column) {
        // Check if the column is already indexed
        $sql = "SHOW INDEX FROM {$this->tableName} WHERE Column_name = :column_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':column_name', $column, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            // The column is not indexed, so create an index
            $indexName = "idx_$column";
            
            $sql = "CREATE INDEX $indexName ON {$this->tableName} ($column)";
            try {
                $this->pdo->exec($sql);
                return true;
            } catch (PDOException $e) {
                $this->logger->logException($e);
                return false;
            }
        }
        
        // The column is already indexed
        return false;
    }
    
    public function dropIndex($column)
    {
        $sql = "DROP INDEX $column ON {$this->tableName}";
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            $this->logger->logException($e);
            return false;
        }
    }
    
    public function selectDistinctColumnWithGroupBy($column)
    {
        $sql = "SELECT $column FROM {$this->tableName} GROUP BY $column";
        
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (PDOException $e) {
            $this->logger->logException($e);
            return false;
        }
    }
    
    /* public function selectDistinctColumnWithGroupBy($column, $limit, $offset) {
        $sql = "SELECT $column FROM {$this->tableName} GROUP BY $column LIMIT :limit OFFSET :offset";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (PDOException $e) {
            $this->logger->logException($e);
            return false;
        }
    } */
    
    /* public function updateRecord($data, $conditions) {
        // $data is an associative array of column => new value pairs
        $setClause = '';
        foreach ($data as $column => $value) {
            $safeColumnName = $this->pdo->quote($column);
            $safeColumnName = trim($safeColumnName, "'");
            $setClause .= "$safeColumnName = :$column, ";
        }
        $setClause = rtrim($setClause, ', '); // Remove the trailing comma
        
        // Build the WHERE conditions
        $whereConditions = array();
        foreach ($conditions as $column => $value) {
            $safeColumnName = $this->pdo->quote($column);
            $safeColumnName = trim($safeColumnName, "'");
            $whereConditions[] = "$safeColumnName = :$column";
        }
        $whereClause = implode(' AND ', $whereConditions);
        
        $sql = "UPDATE {$this->tableName} SET $setClause WHERE $whereClause";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            // Bind parameters for the SET clause
            foreach ($data as $column => $value) {
                $stmt->bindParam(":$column", $value);
            }
            // Bind parameters for the WHERE clause
            foreach ($conditions as $column => $value) {
                $stmt->bindParam(":$column", $value);
            }
            $stmt->execute();
            return $stmt->rowCount(); // Returns the number of affected rows
        } catch (PDOException $e) {
            $this->logger->logException($e);
            return false;
        }
    } */
    
    // Update record
    public function updateRecord($data, $conditions) {
        // $data is an associative array of column => new value pairs
        $setClause = '';
        $setParams = array();
        
        foreach ($data as $column => $value) {
            $safeColumnName = $this->pdo->quote($column);
            $safeColumnName = trim($safeColumnName, "'");
            $setClause .= "$safeColumnName = :set_$column, ";
            $setParams[":set_$column"] = $value; // Use a unique parameter name
        }
        
        $setClause = rtrim($setClause, ', '); // Remove the trailing comma
        
        // Build the WHERE conditions
        $whereConditions = array();
        $whereParams = array();
        
        foreach ($conditions as $column => $value) {
            $safeColumnName = $this->pdo->quote($column);
            $safeColumnName = trim($safeColumnName, "'");
            $whereConditions[] = "$safeColumnName = :where_$column";
            $whereParams[":where_$column"] = $value; // Use a unique parameter name
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        $sql = "UPDATE {$this->tableName} SET $setClause WHERE $whereClause";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $params = array_merge($setParams, $whereParams);
            $stmt->execute($params);
            return $stmt->rowCount(); // Returns the number of affected rows
        } catch (PDOException $e) {
            $this->logger->logException($e);
            return false;
        }
    }
    
    // Select the last record from the table with a WHERE condition
    public function selectLastRecordWhere($conditions, $orderByColumn = 'id') {
        $whereConditions = array();
        
        foreach ($conditions as $column => $value) {
            // Ensure that the column name is safe to use
            $safeColumnName = $this->pdo->quote($column);
            $safeColumnName = trim($safeColumnName, "'");
            $whereConditions[] = "$safeColumnName = '$value'";
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        // Use ORDER BY and DESC to sort in descending order based on the specified column
        $orderByColumn = $this->pdo->quote($orderByColumn);
        $orderByColumn = trim($orderByColumn, "'");
        
        $sql = "SELECT * FROM {$this->tableName} WHERE $whereClause ORDER BY $orderByColumn DESC LIMIT 1";
        
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->logException($e);
            print_r($e->getMessage());
            return false;
        }
    }
    
    public function updateMultipleRecords($data, $conditions) {
        if (empty($data) || empty($conditions)) {
            return false; // No data or conditions to update
        }
        
        // Build the SET clause for updating multiple columns
        $setClause = '';
        $setParams = array();
        foreach ($data as $column => $value) {
            $safeColumnName = $this->pdo->quote($column);
            $safeColumnName = trim($safeColumnName, "'");
            $setClause .= "$safeColumnName = :set_$column, ";
            $setParams[":set_$column"] = $value; // Use unique parameter names for each column
        }
        
        $setClause = rtrim($setClause, ', '); // Remove the trailing comma
        
        // Build the WHERE conditions for the update
        $whereConditions = array();
        $whereParams = array();
        foreach ($conditions as $column => $value) {
            $safeColumnName = $this->pdo->quote($column);
            $safeColumnName = trim($safeColumnName, "'");
            $whereConditions[] = "$safeColumnName = :where_$column";
            $whereParams[":where_$column"] = $value; // Use unique parameter names for each condition
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        $sql = "UPDATE {$this->tableName} SET $setClause WHERE $whereClause";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $params = array_merge($setParams, $whereParams);
            $stmt->execute($params);
            return $stmt->rowCount(); // Returns the number of affected rows
        } catch (PDOException $e) {
            $this->logger->logException($e);
            return false;
        }
    }
    
}

