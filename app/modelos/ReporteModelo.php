<?php

namespace App\modelos;

use App\Config\Database;
use PDO;

require_once 'app/Config/Database.php';

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
                    SUM(CASE WHEN f.estado_ficha = 'Cerrado'    THEN 1 ELSE 0 END) AS cerradas
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
            'cerradas'    => (int)($row['cerradas']   ?? 0),
            'efectividad' => $total > 0 ? round(($atendidas / $total) * 100, 1) : 0
        ];
    }
}
