<?php

class HomeController {
    public function index() {
        // Carga la vista home (anteriormente index.html)
        require_once 'app/Views/home.php';
    }
}
