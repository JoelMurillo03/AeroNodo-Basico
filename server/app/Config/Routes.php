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

// ===== Rutas API (para consumo desde frontend) =====
$routes->post('api/sensores', 'SensoresController::store');
$routes->get('api/sensores', 'SensoresController::index');
$routes->get('api/historial', 'SensoresController::historial');
$routes->get('api/alertas', 'AlertasController::index');
$routes->get('api/dispositivo', 'DispositivoController::index');
$routes->post('api/dispositivo/update', 'DispositivoController::update');

// Nuevas rutas API del sidebar
$routes->get('api/dispositivo/historial', 'DispositivoController::historial');
$routes->get('api/configuraciones', 'ConfiguracionController::getConfigs');

// ===== Rutas para vistas (páginas del sidebar) =====
$routes->get('sensores/ultimo', 'SensoresController::latest');
$routes->get('sensores/recientes/(:num)', 'SensoresController::recent/$1');
$routes->get('sensores/(:num)', 'SensoresController::show/$1');
$routes->put('sensores/(:num)', 'SensoresController::update/$1');
$routes->delete('sensores/(:num)', 'SensoresController::delete/$1');

$routes->get('estadisticas/resumen', 'EstadisticasController::resumen');

// ===== Nuevas rutas para las secciones del Sidebar =====
$routes->get('historial', 'HistorialController::index');
$routes->get('historial/exportar', 'HistorialController::exportar');

$routes->get('graficas', 'GraficasController::index');

$routes->get('alertas', 'AlertasController::listado');
$routes->delete('alertas/(:num)', 'AlertasController::delete/$1');
$routes->post('alertas/marcar-leida/(:num)', 'AlertasController::marcarLeida/$1');

$routes->get('dispositivo', 'DispositivoController::detalle');

$routes->get('configuracion', 'ConfiguracionController::index');
$routes->post('configuracion/guardar', 'ConfiguracionController::guardar');

$routes->get('acerca-de', 'AcercaDeController::index');

// ===== Endpoint para exportar datos desde el dashboard =====
$routes->get('dashboard/exportar', 'HistorialController::exportar');

// ===== Carga de rutas específicas por entorno (si existen) =====
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}