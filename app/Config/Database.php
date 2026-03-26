<?php

namespace App\Config;

use PDO;
use PDOException;

class Database {
    private $host = "localhost";
    private $db_name = "ven911_fichas";
    private $username = "root";
    private $password = "";
    private $conn;

    /**
     * Obtiene la conexión a la base de datos mediante PDO.
     *
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
                PDO::ATTR_EMULATE_PREPARES   => false,                  
            ];
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {

            die("Error de conexión a la base de datos: " . $exception->getMessage());
        }

        return $this->conn;
    }
}
