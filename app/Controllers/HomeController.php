<?php

class HomeController {
    public function home() {
        // Carga la vista home (anteriormente index.html)
        require_once 'app/Views/home.php';
    }
}
