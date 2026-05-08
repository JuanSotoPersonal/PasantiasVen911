<?php
/**
 * MODELO: HomeModelo
 * Propósito: Centralizar la extracción de métricas y estadísticas para el Dashboard.
 * Proporciona datos específicos segmentados por rol de usuario.
 */

namespace App\modelos;

use App\Config\Database;
use PDO;

require_once 'app/Config/Database.php';

class HomeModelo {
    private $conexion;

    public function __construct() {
        $database = new Database();
        $this->conexion = $database->obtenerConexion();
    }

    /**
     * Estadísticas para Administrador: Visión global del sistema.
     */
    public function obtenerResumenAdmin(): array {
        // 1. Usuarios por rol
        $stmt = $this->conexion->query("SELECT r.nombre as nombre_rol, COUNT(u.id) as total 
                                        FROM roles r LEFT JOIN usuarios u ON r.id = u.rol_id 
                                        GROUP BY r.id, r.nombre");
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Fichas por estado
        $stmt = $this->conexion->query("SELECT estado_ficha, COUNT(*) as total FROM fichas_emergencia GROUP BY estado_ficha");
        $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Top Tipos de Emergencia
        $stmt = $this->conexion->query("SELECT t.nombre, COUNT(f.id) as total 
                                        FROM tipos_emergencia t 
                                        JOIN casos c ON t.id = c.tipo_emergencia_id 
                                        JOIN fichas_emergencia f ON c.id = f.caso_id 
                                        GROUP BY t.id, t.nombre ORDER BY total DESC LIMIT 5");
        $emergencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'roles' => $roles,
            'estados' => $estados,
            'emergencias' => $emergencias
        ];
    }

    /**
     * Estadísticas para Operador: Enfoque en su productividad personal.
     */
    public function obtenerResumenOperador(int $userId): array {
        // 1. Mis fichas hoy
        $stmt = $this->conexion->prepare("SELECT COUNT(*) FROM fichas_emergencia WHERE id_user = :uid AND fecha_creacion >= CURDATE()");
        $stmt->execute(['uid' => $userId]);
        $hoy = $stmt->fetchColumn();

        // 2. Mis fichas por estado
        $stmt = $this->conexion->prepare("SELECT estado_ficha, COUNT(*) as total FROM fichas_emergencia WHERE id_user = :uid GROUP BY estado_ficha");
        $stmt->execute(['uid' => $userId]);
        $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Actividad últimos 7 días
        $stmt = $this->conexion->prepare("SELECT DATE(fecha_creacion) as fecha, COUNT(*) as total 
                                          FROM fichas_emergencia 
                                          WHERE id_user = :uid AND fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                                          GROUP BY DATE(fecha_creacion) ORDER BY fecha ASC");
        $stmt->execute(['uid' => $userId]);
        $semana = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total_hoy' => $hoy,
            'estados' => $estados,
            'semana' => $semana
        ];
    }

    /**
     * Estadísticas para Despachador: Enfoque en atención y organismos.
     */
    public function obtenerResumenDespachador(int $userId): array {
        // 1. Fichas pendientes globales (lo que tiene que atender)
        $stmt = $this->conexion->query("SELECT COUNT(*) FROM fichas_emergencia WHERE estado_ficha = 'Pendiente'");
        $pendientes = $stmt->fetchColumn();

        // 2. Mis despachos activos (En camino / En sitio)
        $stmt = $this->conexion->prepare("SELECT COUNT(*) FROM despachos_organismos 
                                          WHERE despachador_id = :uid AND estatus_despacho IN ('Asignado', 'En Camino', 'En Sitio')");
        $stmt->execute(['uid' => $userId]);
        $activos = $stmt->fetchColumn();

        // 3. Organismos más solicitados (Top 5)
        $stmt = $this->conexion->query("SELECT o.nombre_organismo, COUNT(d.id) as total 
                                        FROM organismos o JOIN despachos_organismos d ON o.id = d.organismo_id 
                                        GROUP BY o.id, o.nombre_organismo ORDER BY total DESC LIMIT 5");
        $organismos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'pendientes_globales' => $pendientes,
            'mis_despachos_activos' => $activos,
            'top_organismos' => $organismos
        ];
    }

    /**
     * Estadísticas para Jefatura: Visión gerencial y geográfica.
     */
    public function obtenerResumenJefatura(): array {
        // 1. Incidentes por Municipio y su estado (Para Barras Apiladas)
        $stmt = $this->conexion->query("SELECT m.nombre_municipio, 
                                               SUM(CASE WHEN f.estado_ficha = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
                                               SUM(CASE WHEN f.estado_ficha IN ('Atendido', 'Cerrado') THEN 1 ELSE 0 END) as resueltos,
                                               COUNT(f.id) as total
                                        FROM municipios m 
                                        JOIN parroquias p ON m.id = p.municipio_id 
                                        JOIN fichas_emergencia f ON p.id = f.parroquia_id 
                                        GROUP BY m.id, m.nombre_municipio ORDER BY total DESC LIMIT 6");
        $municipios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Comparativa Temporal: Volumen por Hora (Hoy vs Ayer)
        // Hoy
        $stmt = $this->conexion->query("SELECT HOUR(fecha_creacion) as hora, COUNT(*) as total 
                                        FROM fichas_emergencia WHERE fecha_creacion >= CURDATE() 
                                        GROUP BY HOUR(fecha_creacion) ORDER BY hora");
        $hoy = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ayer
        $stmt = $this->conexion->query("SELECT HOUR(fecha_creacion) as hora, COUNT(*) as total 
                                        FROM fichas_emergencia 
                                        WHERE fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) 
                                        AND fecha_creacion < CURDATE()
                                        GROUP BY HOUR(fecha_creacion) ORDER BY hora");
        $ayer = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Distribución de Cierres (Calidad)
        $stmt = $this->conexion->query("SELECT tipo_motivo_cierre as motivo, COUNT(*) as total 
                                        FROM fichas_emergencia WHERE estado_ficha = 'Cerrado' AND tipo_motivo_cierre IS NOT NULL
                                        GROUP BY tipo_motivo_cierre");
        $cierres = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 4. Métricas Rápidas (KPIs)
        // Fichas creadas hoy
        $stmt = $this->conexion->query("SELECT COUNT(*) FROM fichas_emergencia WHERE fecha_creacion >= CURDATE()");
        $total_hoy = $stmt->fetchColumn();

        // Fichas atendidas hoy (Métrica de efectividad real)
        $stmt = $this->conexion->query("SELECT COUNT(*) FROM fichas_emergencia WHERE estado_ficha = 'Atendido' AND fecha_creacion >= CURDATE()");
        $atendidas_hoy = $stmt->fetchColumn();

        return [
            'municipios' => $municipios,
            'comparativa' => ['hoy' => $hoy, 'ayer' => $ayer],
            'cierres' => $cierres,
            'kpis' => [
                'total_hoy' => $total_hoy,
                'atendidas_hoy' => $atendidas_hoy,
                'efectividad' => $total_hoy > 0 ? round(($atendidas_hoy / $total_hoy) * 100, 1) : 0
            ]
        ];
    }
}
