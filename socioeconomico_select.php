<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

// Por enquanto, reutiliza a listagem existente como seletor básico
// Em seguida podemos trocar para uma tela dedicada de seleção de criança
$controller = new SocioeconomicoController();
$controller->index();
