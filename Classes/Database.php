<?php
namespace Classes;

use PDO;
use PDOException;

class Database
{
    private $pdo;
    
    public function __construct() {
        $config = require 'config.php';
        
        try {
            $this->pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['database']}",
                $config['username'],
                $config['password']
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error connecting to DB: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

?>