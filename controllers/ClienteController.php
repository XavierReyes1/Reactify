<?php

namespace Controllers;

use Model\Respuesta;

use Model\Areas;
use MVC\Router;

class ClienteController
{

      public static function index(Router $router)
    {
        $router->render('cliente/index');
    }

    public static function apectos(Router $router)
    {
        $router->render('cliente/aspectos');
    }

    public static function agradecimientos(Router $router)
    {
        $router->render('cliente/agradecimiento');
    }

    public static function comentario(Router $router)
    {
        $router->render('cliente/comentario');
    }



    public static function obtenerAreas()
    {
        // Obtener todas las áreas usando ActiveRecord
        $areas = Areas::all();

        if (!empty($areas)) {
            http_response_code(200); // OK
            echo json_encode($areas);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(["message" => "No se encontraron áreas."]);
        }
    }

    public static function guardarRespuesta()
    {
        // Obtener los datos enviados en la solicitud (se esperan en formato JSON)
        $data = json_decode(file_get_contents("php://input"), true);

        // Validar que los datos necesarios estén presentes
        if (
            empty($data['nivel_satisfaccion']) ||
            empty($data['areas_id'])
        ) {
            http_response_code(400); // Bad Request
            echo json_encode(["message" => "Datos incompletos. Se requiere nivel_satisfaccion y areas_id."]);
            return;
        }

        // Crear una nueva instancia de Respuesta
        $respuesta = new Respuesta([
            'areas_id' => $data['areas_id'],
            'nivel_satisfaccion' => $data['nivel_satisfaccion'],
            'comentario' => $data['comentario'] ?? null
        ]);

        // Guardar la respuesta en la base de datos
        $resultado = $respuesta->guardar();

        if ($resultado['resultado']) {
            http_response_code(201); // Created
            echo json_encode([
                "message" => "Respuesta guardada exitosamente.",
                "id_respuesta" => $resultado['id']
            ]);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(["message" => "Error al guardar la respuesta."]);
        }
    }
}