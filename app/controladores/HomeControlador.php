<?php
/**
 * CONTROLADOR: HomeControlador
 * Propósito: Gestionar el aterrizaje de los usuarios tras el inicio de sesión.
 * Consolida las estadísticas del sistema para el Dashboard principal.
 */

require_once 'app/modelos/UsuarioModelo.php';
use App\modelos\UsuarioModelo;

class HomeControlador {

    // ///////////////////////////////////////////////////////////////////
    // 1. SEGURIDAD Y CONSTRUCTOR
    // ///////////////////////////////////////////////////////////////////

    /**
     * Valida que el usuario tenga una sesión activa antes de permitir el acceso al inicio.
     */
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth');
            exit;
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 2. RENDERIZADO (DASHBOARD)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Procesa y muestra la vista de inicio, consolidando contadores de gestión básicos.
     */
    public function index() {
        try {
            $usuarioModelo = new UsuarioModelo();
            $estadisticas = $usuarioModelo->contarPorEstado();

            $datos = [
                'activo'   => 0,
                'inactivo' => 0
            ];

            // 2.1 Mapeo dinámico de contadores de usuarios según su estado actual
            foreach ($estadisticas as $dato_estadistico) {
                $datos[$dato_estadistico['estado']] = (int)$dato_estadistico['total'];
            }

            // 2.2 Carga del contenedor principal de la vista home 
            require_once 'app/vista/home.php';
        } catch (\Exception $e) {
            error_log("[HomeControlador] Error en index: " . $e->getMessage());
            die("Ocurrió un error inesperado al cargar el inicio.");
        }
    }
}
