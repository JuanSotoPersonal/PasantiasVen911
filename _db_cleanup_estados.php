<?php
require 'app/Config/Database.php';

try {
    $db = new \App\Config\Database();
    $conn = $db->obtenerConexion();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Iniciando limpieza del módulo de Estado en la BD...\n";

    // 1. Eliminar clave foránea en municipios
    $stmt = $conn->query("SHOW CREATE TABLE `municipios`");
    $createTableSql = $stmt->fetchColumn(1);
    
    if (strpos($createTableSql, 'fk_municipio_estado') !== false) {
        $conn->exec("ALTER TABLE `municipios` DROP FOREIGN KEY `fk_municipio_estado`;");
        echo "Clave foránea 'fk_municipio_estado' eliminada.\n";
    }

    // 2. Eliminar columna estado_id en municipios
    $stmt = $conn->query("SHOW COLUMNS FROM `municipios` LIKE 'estado_id'");
    if ($stmt->rowCount() > 0) {
        $conn->exec("ALTER TABLE `municipios` DROP COLUMN `estado_id`;");
        echo "Columna 'estado_id' eliminada de municipios.\n";
    }

    // 3. Eliminar tabla estados
    $conn->exec("DROP TABLE IF EXISTS `estados`;");
    echo "Tabla 'estados' eliminada.\n";

    // 4. Limpiar cache por precaución
    if (file_exists('app/Helpers/Cache.php')) {
        require_once 'app/Helpers/Cache.php';
        if (class_exists('\App\Helpers\Cache')) {
            \App\Helpers\Cache::limpiarTodo();
            echo "Caché de consultas purgada.\n";
        }
    }

    echo "Limpieza de base de datos finalizada con éxito.\n";
} catch (Exception $e) {
    echo "Error durante la limpieza: " . $e->getMessage() . "\n";
}
