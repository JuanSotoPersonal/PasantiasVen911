<?php
/**
 * CONTROLADOR: AyudaControlador
 * Propósito: Renderizar la vista de Preguntas Frecuentes (FAQ).
 * Es un controlador simple estático sin interacción de Base de Datos.
 */

class AyudaControlador {

    public function __construct() {
        // Validar sesión activa (Regla RBAC)
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth');
            exit;
        }
    }

    /**
     * Renderiza la vista principal de ayuda (FAQ estáticas)
     */
    public function index() {
        require_once 'app/vista/ayuda/index.php';
    }
}
