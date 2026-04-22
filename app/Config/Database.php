<?php

namespace App\Config;

use PDO;
use PDOException;

class Database {
    private $servidor   = "localhost";
    private $nombre_bd  = "ficha_ven_911";
    private $usuario    = "root";
    private $contrasena = "";
    private $conexion;

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
