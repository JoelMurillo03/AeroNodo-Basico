<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// Página principal
$routes->get('/', 'Home::index');

$routes->post('api/sensores', 'SensoresController::store');
$routes->get('api/sensores', 'SensoresController::index');
$routes->get('api/historial', 'SensoresController::historial');
$routes->get('api/alertas', 'AlertasController::index');
$routes->get('api/dispositivo', 'DispositivoController::index');
$routes->post('api/dispositivo/update', 'DispositivoController::update');

$routes->get('sensores/ultimo', 'SensoresController::latest');
$routes->get('sensores/recientes/(:num)', 'SensoresController::recent/$1');
$routes->get('sensores/(:num)', 'SensoresController::show/$1');
$routes->put('sensores/(:num)', 'SensoresController::update/$1');
$routes->delete('sensores/(:num)', 'SensoresController::delete/$1');

$routes->get('estadisticas/resumen', 'EstadisticasController::resumen');


if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}