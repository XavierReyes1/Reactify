<?php
// api/admin/areas.php

include_once '../../includes/database.php';

// Iniciar sesión y verificar autenticación
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401); // Unauthorized
    echo json_encode(array("message" => "Acceso no autorizado. Se requiere iniciar sesión."));
    exit();
}


header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

if ($db === null) {
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true); // Para POST, PUT
// Para DELETE, el ID usualmente viene en la URL, pero aquí lo simplificaremos o se puede pasar en el cuerpo.
// Para GET, los parámetros vienen en la URL.

// Obtener el ID del path si está presente (ej. /api/admin/areas.php/1)
$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
$path_parts = explode('/', trim($path_info, '/'));
$area_id_from_path = isset($path_parts[0]) && is_numeric($path_parts[0]) ? intval($path_parts[0]) : null;


try {
    switch ($method) {
        case 'GET':
            // Si se proporciona un ID en el path (ej: /areas.php/123), obtener esa área específica
            if ($area_id_from_path !== null) {
                 $stmt = $db->prepare("SELECT id, nombre FROM areas WHERE id = :id");
                 $stmt->bindParam(':id', $area_id_from_path);
                 $stmt->execute();
                 $area = $stmt->fetch(PDO::FETCH_ASSOC);
                 if ($area) {
                    http_response_code(200);
                    echo json_encode($area);
                 } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Área no encontrada."));
                 }
            } else {
                // Obtener todas las áreas
                $query = "SELECT id, nombre FROM areas ORDER BY nombre ASC";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                http_response_code(200);
                echo json_encode($areas);
            }
            break;

        case 'POST': // Crear nueva área
            if (!isset($input['nombre']) || empty(trim($input['nombre']))) {
                http_response_code(400);
                echo json_encode(array("message" => "El nombre del área es requerido."));
                exit();
            }
            $nombre = htmlspecialchars(strip_tags(trim($input['nombre'])));

            // Verificar si el área ya existe
            $check_query = "SELECT id FROM areas WHERE nombre = :nombre";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(":nombre", $nombre);
            $check_stmt->execute();
            if ($check_stmt->rowCount() > 0) {
                http_response_code(409); // Conflict
                echo json_encode(array("message" => "El área con este nombre ya existe."));
                exit();
            }

            $query = "INSERT INTO areas (nombre) VALUES (:nombre)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nombre', $nombre);

            if ($stmt->execute()) {
                $last_id = $db->lastInsertId();
                http_response_code(201); // Created
                echo json_encode(array("message" => "Área creada exitosamente.", "id" => $last_id, "nombre" => $nombre));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => "Error al crear el área."));
            }
            break;

        case 'PUT': // Actualizar área existente
            // El ID puede venir en el path o en el cuerpo JSON. Priorizar el path.
            $id_to_update = $area_id_from_path;
            if ($id_to_update === null && isset($input['id'])) {
                 $id_to_update = filter_var($input['id'], FILTER_VALIDATE_INT);
            }

            if ($id_to_update === null || $id_to_update === false) {
                http_response_code(400);
                echo json_encode(array("message" => "ID de área inválido o no proporcionado."));
                exit();
            }
            if (!isset($input['nombre']) || empty(trim($input['nombre']))) {
                http_response_code(400);
                echo json_encode(array("message" => "El nuevo nombre del área es requerido."));
                exit();
            }
            $nombre_nuevo = htmlspecialchars(strip_tags(trim($input['nombre'])));

            // Verificar si el nuevo nombre ya existe en otra área (opcional, depende de la lógica de negocio)
            // $check_query = "SELECT id FROM areas WHERE nombre = :nombre AND id != :id";
            // ...

            $query = "UPDATE areas SET nombre = :nombre WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nombre', $nombre_nuevo);
            $stmt->bindParam(':id', $id_to_update);

            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    http_response_code(200); // OK
                    echo json_encode(array("message" => "Área actualizada exitosamente.", "id" => $id_to_update, "nombre" => $nombre_nuevo));
                } else {
                    http_response_code(404); // Not Found (o 304 Not Modified si el nombre era el mismo)
                    echo json_encode(array("message" => "Área no encontrada o no se realizaron cambios."));
                }
            } else {
                http_response_code(500);
                echo json_encode(array("message" => "Error al actualizar el área."));
            }
            break;

        case 'DELETE': // Eliminar área
            // El ID puede venir en el path o en el cuerpo JSON. Priorizar el path.
            $id_to_delete = $area_id_from_path;
             if ($id_to_delete === null && isset($input['id'])) { // Si no vino en path, buscar en cuerpo
                 $id_to_delete = filter_var($input['id'], FILTER_VALIDATE_INT);
            }

            if ($id_to_delete === null || $id_to_delete === false) {
                http_response_code(400);
                echo json_encode(array("message" => "ID de área inválido o no proporcionado para eliminar."));
                exit();
            }

            // Opcional: Verificar si el área tiene respuestas asociadas antes de eliminar
            $check_respuestas_query = "SELECT COUNT(*) as count FROM respuestas WHERE areas_id = :id";
            $check_stmt = $db->prepare($check_respuestas_query);
            $check_stmt->bindParam(':id', $id_to_delete);
            $check_stmt->execute();
            $result = $check_stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && $result['count'] > 0) {
                http_response_code(409); // Conflict
                echo json_encode(array("message" => "No se puede eliminar el área porque tiene respuestas asociadas. Considere desactivarla o reasignar las respuestas."));
                exit();
            }


            $query = "DELETE FROM areas WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id_to_delete);

            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    http_response_code(200); // OK (o 204 No Content)
                    echo json_encode(array("message" => "Área eliminada exitosamente."));
                } else {
                    http_response_code(404); // Not Found
                    echo json_encode(array("message" => "Área no encontrada para eliminar."));
                }
            } else {
                http_response_code(500);
                echo json_encode(array("message" => "Error al eliminar el área."));
            }
            break;

        default:
            http_response_code(405); // Method Not Allowed
            echo json_encode(array("message" => "Método no permitido."));
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Error de base de datos: " . $e->getMessage()));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Error inesperado: " . $e->getMessage()));
}
?>
