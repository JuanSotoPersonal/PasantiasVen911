<?php
require_once 'app/modelos/UsuarioModelo.php';
use App\modelos\UsuarioModelo;

class HomeControlador {

    //--------------------------------------------------------------------
    // Constructor de Blindaje RBAC
    //--------------------------------------------------------------------
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth');
            exit;
        }
    }

    //--------------------------------------------------------------------
    // Muestra la pantalla de inicio con estadísticas básicas
    //--------------------------------------------------------------------

    public function index() {
        $usuarioModelo = new UsuarioModelo();
        $estadisticas = $usuarioModelo->contarPorEstado();

        $datos = [
            'activo'   => 0,
            'inactivo' => 0
        ];

        foreach ($estadisticas as $dato_estadistico) {
            $datos[$dato_estadistico['estado']] = (int)$dato_estadistico['total'];
        }

        // Carga la vista home 
        require_once 'app/vista/home.php';
    }
}
