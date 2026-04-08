<?php

class HomeController {

    //--------------------------------------------------------------------
    // Muestra la pantalla de inicio
    //--------------------------------------------------------------------

    public function index() {
        // Carga la vista home 
        require_once 'app/Views/home.php';
    }
}
