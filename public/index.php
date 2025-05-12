<?php 

require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
use Controllers\ClienteController;

$router = new Router();


// Login
$router->get('/index', [ClienteController::class, 'index']);
$router->get('/aspectos', [ClienteController::class, 'aspectos']);
$router->get('/agradecimientos', [ClienteController::class, 'agradecimientos']);
$router->get('/comentario', [ClienteController::class, 'comentario']);



// Rutas para ClienteController
$router->get('/cliente/obtener_areas', [ClienteController::class, 'obtenerAreas']);
$router->post('/cliente/guardar_respuesta', [ClienteController::class, 'guardarRespuesta']);

// Crear Cuenta

$router->comprobarRutas();