<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// app/Config/Routes.php — adicione:
$routes->get('diario/buscar', 'DiarioController::buscar');
$routes->get('/', 'DiarioController::index');
