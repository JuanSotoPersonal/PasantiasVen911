<?php
require 'app/Config/Database.php';

try {
    $db = new \App\Config\Database();
    $conn = $db->obtenerConexion();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Iniciando actualización de BD...\n";



    // 3. Comunas
    $conn->exec("CREATE TABLE IF NOT EXISTS `comunas` (
        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `parroquia_id` int(10) UNSIGNED NOT NULL,
        `nombre_comuna` varchar(150) NOT NULL,
        `descripcion` varchar(256) DEFAULT NULL,
        `estado` tinyint(1) DEFAULT 1,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`parroquia_id`) REFERENCES `parroquias`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    echo "Tabla 'comunas' verificada/creada.\n";

    // 4. Sectores
    $conn->exec("CREATE TABLE IF NOT EXISTS `sectores` (
        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `comuna_id` int(10) UNSIGNED NOT NULL,
        `nombre_sector` varchar(150) NOT NULL,
        `descripcion` varchar(256) DEFAULT NULL,
        `estado` tinyint(1) DEFAULT 1,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`comuna_id`) REFERENCES `comunas`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    echo "Tabla 'sectores' verificada/creada.\n";

    // 5. Cuadrantes de Paz
    $conn->exec("CREATE TABLE IF NOT EXISTS `cuadrantes_paz` (
        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `sector_id` int(10) UNSIGNED NOT NULL,
        `organismo_id` int(10) UNSIGNED DEFAULT NULL,
        `nombre_cuadrante` varchar(150) NOT NULL,
        `descripcion` varchar(256) DEFAULT NULL,
        `estado` tinyint(1) DEFAULT 1,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`sector_id`) REFERENCES `sectores`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`organismo_id`) REFERENCES `organismos`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    echo "Tabla 'cuadrantes_paz' verificada/creada.\n";

    // 6. Modificar fichas_emergencia
    $stmt = $conn->query("SHOW COLUMNS FROM `fichas_emergencia` LIKE 'comuna_id'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE `fichas_emergencia`
            ADD `comuna_id` int(10) UNSIGNED DEFAULT NULL AFTER `parroquia_id`,
            ADD `sector_id` int(10) UNSIGNED DEFAULT NULL AFTER `comuna_id`,
            ADD CONSTRAINT `fk_ficha_comuna` FOREIGN KEY (`comuna_id`) REFERENCES `comunas`(`id`) ON DELETE SET NULL,
            ADD CONSTRAINT `fk_ficha_sector` FOREIGN KEY (`sector_id`) REFERENCES `sectores`(`id`) ON DELETE SET NULL;");
        echo "Tabla 'fichas_emergencia' modificada (comuna_id, sector_id agregados).\n";
    }

    // 7. Modificar despachos_organismos
    $stmt = $conn->query("SHOW COLUMNS FROM `despachos_organismos` LIKE 'cuadrante_id'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE `despachos_organismos`
            ADD `cuadrante_id` int(10) UNSIGNED DEFAULT NULL AFTER `organismo_id`,
            ADD CONSTRAINT `fk_despacho_cuadrante` FOREIGN KEY (`cuadrante_id`) REFERENCES `cuadrantes_paz`(`id`) ON DELETE SET NULL;");
        echo "Tabla 'despachos_organismos' modificada (cuadrante_id agregado).\n";
    }

    // Limpiar cache por precaución (usando file system o simple si hay clase Cache)
    if (file_exists('app/Helpers/Cache.php')) {
        require_once 'app/Helpers/Cache.php';
        if (class_exists('\App\Helpers\Cache')) {
            \App\Helpers\Cache::limpiarTodo();
        }
    }

    echo "Actualización de BD exitosa.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
