<?php

class AuthController {
    
    /**
     * Muestra la pantalla de inicio de sesión
     */
    public function index() {
        require_once 'app/Views/login.php';
    }

}
