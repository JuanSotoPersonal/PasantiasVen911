<?php

namespace App\Config;

use PDO;
use PDOException;

class Database {
    private $servidor;
    private $nombre_bd;
    private $usuario;
    private $contrasena;
    private $conexion;

    public function __construct() {
        $this->servidor   = getenv('DB_HOST') ?: "localhost";
        $this->nombre_bd  = getenv('DB_NAME') ?: "ficha_ven_911";
        $this->usuario    = getenv('DB_USER') ?: "root";
        $this->contrasena = getenv('DB_PASS') ?: "";
    }

    /**
     * Obtiene la conexión a la base de datos mediante PDO.
     *
     * @return PDO
     * @throws \Exception Si falla la conexión
     */
    public function obtenerConexion() {
        $this->conexion = null;

        try {
            $dsn = "mysql:host=" . $this->servidor . ";dbname=" . $this->nombre_bd . ";charset=utf8mb4";
            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
                PDO::ATTR_EMULATE_PREPARES   => false,                  
            ];
            $this->conexion = new PDO($dsn, $this->usuario, $this->contrasena, $opciones);
            
        } catch(PDOException $e) {
            error_log("[Database] Error de conexión: " . $e->getMessage());
            throw new \Exception("Error de conexión a la base de datos.");
        }

        return $this->conexion;
    }
}
