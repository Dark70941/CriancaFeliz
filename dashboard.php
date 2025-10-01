<?php
// Carregar bootstrap MVC
require_once 'bootstrap.php';

// Instanciar controller do dashboard
$dashboardController = new DashboardController();

// Exibir dashboard
$dashboardController->index();
