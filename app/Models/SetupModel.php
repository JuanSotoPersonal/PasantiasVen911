<?php

namespace App\Models;

use App\Config\Database;
use PDO;

require_once 'app/Config/Database.php';

class SetupModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    //--------------------------------------------------------------------
    // Obtiene todas las preguntas de seguridad predefinidas.
    //--------------------------------------------------------------------
    public function getSecurityQuestions(): array {
        $query = "SELECT * FROM preguntas_seguridad ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //--------------------------------------------------------------------
    // Valida si la llave de activación es correcta.
    //--------------------------------------------------------------------
    public function validateActivationKey(string $key): bool {
        $query = "SELECT COUNT(*) FROM configuracion_sistema WHERE llave_activacion = :key";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':key', $key, PDO::PARAM_STR);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    //--------------------------------------------------------------------
    // Obtiene la llave de activación (para debug o referencia, aunque debería ser secreta).
    //--------------------------------------------------------------------
    public function getActivationKey(): string {
        $query = "SELECT llave_activacion FROM configuracion_sistema LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return (string)$stmt->fetchColumn();
    }
}
