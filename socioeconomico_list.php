<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

// Instanciar controller socioeconômico
$socioeconomicoController = new SocioeconomicoController();

// Exibir lista
$socioeconomicoController->index();
