<?php

namespace App\modelos;

use App\Config\Database;
use PDO;
use App\Helpers\Cache;

require_once 'app/Config/Database.php';
require_once 'app/Helpers/Cache.php';

/**
 * ReporteModelo - Gestión de consultas avanzadas y filtrado dinámico
 */
class ReporteModelo {

    private $conexion;

    public function __construct() {
        $db = new Database();
        $this->conexion = $db->obtenerConexion();
    }

    public function getConexion() {
        return $this->conexion;
    }

    /**
     * Obtener fichas filtradas según criterios dinámicos
     */
    public function obtenerFichasFiltradas(array $filtros): array {
        $sql = "SELECT f.id,
                       f.id AS codigo_ficha,
                       f.estado_ficha,
                       f.fecha_creacion,
                       p.nombre_parroquia,
                       m.nombre_municipio,
                       u.nombre_completo AS nombre_operador,
                       e.nombre          AS nombre_emergencia,
                       c.nombre_caso     AS nombre_caso
                FROM fichas_emergencia f
                JOIN parroquias p        ON f.parroquia_id       = p.id
                JOIN municipios m        ON p.municipio_id       = m.id
                JOIN usuarios u          ON f.id_user            = u.id
                JOIN casos c             ON f.caso_id            = c.id
                JOIN tipos_emergencia e  ON c.tipo_emergencia_id = e.id
                WHERE 1=1";

        $params = [];

        // Filtro por Rango de Fechas
        if (!empty($filtros['desde'])) {
            $sql .= " AND f.fecha_creacion >= :desde";
            $params[':desde'] = $filtros['desde'] . ' 00:00:00';
        }
        if (!empty($filtros['hasta'])) {
            $sql .= " AND f.fecha_creacion <= :hasta";
            $params[':hasta'] = $filtros['hasta'] . ' 23:59:59';
        }

        // Filtro por Municipio
        if (!empty($filtros['municipio_id'])) {
            $sql .= " AND m.id = :municipio_id";
            $params[':municipio_id'] = $filtros['municipio_id'];
        }

        // Filtro por Tipo de Emergencia
        if (!empty($filtros['tipo_emergencia_id'])) {
            $sql .= " AND c.tipo_emergencia_id = :tipo_emergencia_id";
            $params[':tipo_emergencia_id'] = $filtros['tipo_emergencia_id'];
        }

        // Filtro por Tipo de Caso
        if (!empty($filtros['caso_id'])) {
            $sql .= " AND f.caso_id = :caso_id";
            $params[':caso_id'] = $filtros['caso_id'];
        }

        // Filtro por Operador
        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND f.id_user = :usuario_id";
            $params[':usuario_id'] = $filtros['usuario_id'];
        }

        // Filtro por Estado
        if (!empty($filtros['estado'])) {
            $sql .= " AND f.estado_ficha = :estado";
            $params[':estado'] = $filtros['estado'];
        }

        $sql .= " ORDER BY f.fecha_creacion DESC LIMIT 500";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener resumen de totales para los filtros aplicados (1 query SQL, sin doble carga).
     */
    public function obtenerResumenFiltrado(array $filtros): array {
        // Reutiliza los mismos filtros, pero con un COUNT directo en SQL para evitar doble carga
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN f.estado_ficha = 'Atendido'   THEN 1 ELSE 0 END) AS atendidas,
                    SUM(CASE WHEN f.estado_ficha = 'Pendiente'  THEN 1 ELSE 0 END) AS pendientes,
                    SUM(CASE WHEN f.estado_ficha = 'En Proceso' THEN 1 ELSE 0 END) AS en_proceso,
                    SUM(CASE WHEN f.estado_ficha = 'Cancelada'  THEN 1 ELSE 0 END) AS canceladas
                FROM fichas_emergencia f
                JOIN parroquias p        ON f.parroquia_id       = p.id
                JOIN municipios m        ON p.municipio_id       = m.id
                JOIN casos c             ON f.caso_id            = c.id
                JOIN tipos_emergencia e  ON c.tipo_emergencia_id = e.id
                WHERE 1=1";

        $params = [];

        if (!empty($filtros['desde']))              { $sql .= " AND f.fecha_creacion >= :desde";              $params[':desde']              = $filtros['desde'] . ' 00:00:00'; }
        if (!empty($filtros['hasta']))              { $sql .= " AND f.fecha_creacion <= :hasta";              $params[':hasta']              = $filtros['hasta'] . ' 23:59:59'; }
        if (!empty($filtros['municipio_id']))       { $sql .= " AND m.id = :municipio_id";                   $params[':municipio_id']       = $filtros['municipio_id']; }
        if (!empty($filtros['tipo_emergencia_id'])) { $sql .= " AND c.tipo_emergencia_id = :tipo_emergencia_id"; $params[':tipo_emergencia_id'] = $filtros['tipo_emergencia_id']; }
        if (!empty($filtros['caso_id']))            { $sql .= " AND f.caso_id = :caso_id";                   $params[':caso_id']            = $filtros['caso_id']; }
        if (!empty($filtros['usuario_id']))         { $sql .= " AND f.id_user = :usuario_id";                $params[':usuario_id']         = $filtros['usuario_id']; }
        if (!empty($filtros['estado']))             { $sql .= " AND f.estado_ficha = :estado";               $params[':estado']             = $filtros['estado']; }

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $total     = (int)($row['total']     ?? 0);
        $atendidas = (int)($row['atendidas'] ?? 0);

        return [
            'total'       => $total,
            'atendidas'   => $atendidas,
            'pendientes'  => (int)($row['pendientes'] ?? 0),
            'en_proceso'  => (int)($row['en_proceso'] ?? 0),
            'canceladas'  => (int)($row['canceladas'] ?? 0),
            'efectividad' => $total > 0 ? round(($atendidas / $total) * 100, 1) : 0
        ];
    }

    /**
     * Obtener lista de usuarios con rol Operador (Rol ID: 2)
     */
    public function obtenerOperadores(): array {
        return Cache::remember('operadores_lista', 3600, function() {
            $sql = "SELECT id, nombre_completo FROM usuarios WHERE rol_id = 2 ORDER BY nombre_completo ASC";
            $stmt = $this->conexion->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        });
    }

    /**
     * Obtener estadísticas de incidentes agrupados por caso y día del mes.
     * 
     * @param int $mes
     * @param int $anio
     * @param string $estado 'Atendido' o 'No Atendido' (Pendiente, En Proceso, Cancelada)
     * @return array
     */
    public function obtenerMatrizAcumuladaMensual(int $mes, int $anio, string $estado): array {
        // Para 'Atendido' filtramos exactamente estado_ficha = 'Atendido'
        // Para 'No Atendido' filtramos todo lo que no sea 'Atendido' (Pendiente, En Proceso, Cancelada)
        $condicionEstado = ($estado === 'Atendido') ? "f.estado_ficha = 'Atendido'" : "f.estado_ficha != 'Atendido'";
        
        $sql = "SELECT 
                    c.id AS caso_id,
                    c.nombre_caso,
                    DAY(f.fecha_creacion) AS dia,
                    COUNT(f.id) AS total_dia
                FROM casos c
                LEFT JOIN fichas_emergencia f ON f.caso_id = c.id 
                    AND MONTH(f.fecha_creacion) = :mes 
                    AND YEAR(f.fecha_creacion) = :anio
                    AND {$condicionEstado}
                WHERE c.estado = 1
                GROUP BY c.id, c.nombre_caso, DAY(f.fecha_creacion)
                ORDER BY c.nombre_caso ASC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':mes'  => $mes,
            ':anio' => $anio
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener listado de despachos mensuales para un estado de ficha específico.
     * 
     * @param int $mes
     * @param int $anio
     * @param string $estado 'Atendido' o 'No Atendido'
     * @return array
     */
    public function obtenerDespachosMensuales(int $mes, int $anio, string $estado): array {
        $condicionEstado = ($estado === 'Atendido') ? "f.estado_ficha = 'Atendido'" : "f.estado_ficha != 'Atendido'";
        
        $sql = "SELECT 
                    f.fecha_creacion,
                    o.nombre_organismo,
                    cp.nombre_cuadrante,
                    m.nombre_municipio,
                    p.nombre_parroquia,
                    c.nombre_caso,
                    d.motivo_cancelacion AS motivo_cancelacion_despacho,
                    f.motivo_cierre AS motivo_cierre_ficha,
                    f.tipo_motivo_cierre AS tipo_motivo_cierre_ficha
                FROM despachos_organismos d
                JOIN fichas_emergencia f ON d.ficha_id = f.id
                JOIN organismos o ON d.organismo_id = o.id
                LEFT JOIN cuadrantes_paz cp ON d.cuadrante_id = cp.id
                JOIN parroquias p ON f.parroquia_id = p.id
                JOIN municipios m ON p.municipio_id = m.id
                JOIN casos c ON f.caso_id = c.id
                WHERE MONTH(f.fecha_creacion) = :mes 
                  AND YEAR(f.fecha_creacion) = :anio
                  AND {$condicionEstado}
                ORDER BY f.fecha_creacion ASC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':mes'  => $mes,
            ':anio' => $anio
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
