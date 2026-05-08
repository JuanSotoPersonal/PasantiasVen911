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
        $sql = "SELECT f.*, 
                       p.nombre_parroquia, 
                       m.nombre_municipio,
                       u.nombre_completo as nombre_operador,
                       e.nombre as nombre_emergencia
                FROM fichas_emergencia f
                JOIN parroquias p ON f.parroquia_id = p.id
                JOIN municipios m ON p.municipio_id = m.id
                JOIN usuarios u ON f.id_user = u.id
                JOIN tipos_emergencia e ON f.tipo_emergencia_id = e.id
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
            $sql .= " AND f.tipo_emergencia_id = :tipo_emergencia_id";
            $params[':tipo_emergencia_id'] = $filtros['tipo_emergencia_id'];
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

        $sql .= " ORDER BY f.fecha_creacion DESC LIMIT 100";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener resumen de totales para los filtros aplicados
     */
    public function obtenerResumenFiltrado(array $filtros): array {
        $fichas = $this->obtenerFichasFiltradas($filtros);
        
        $total = count($fichas);
        $atendidas = 0;
        $pendientes = 0;
        $cerradas = 0;

        foreach ($fichas as $f) {
            if ($f['estado_ficha'] === 'Atendido') $atendidas++;
            elseif ($f['estado_ficha'] === 'Pendiente') $pendientes++;
            elseif ($f['estado_ficha'] === 'Cerrado') $cerradas++;
        }

        return [
            'total' => $total,
            'atendidas' => $atendidas,
            'pendientes' => $pendientes,
            'cerradas' => $cerradas,
            'efectividad' => $total > 0 ? round(($atendidas / $total) * 100, 1) : 0
        ];
    }
}
