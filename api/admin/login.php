<?php
// api/admin/login.php

// Incluir configuración de base de datos (aunque no se usa directamente aquí para el login simulado)
// include_once '../config/database.php';

// Iniciar sesión PHP para manejar el estado de autenticación
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Obtener los datos enviados (esperados en JSON)
$data = json_decode(file_get_contents("php://input"));

// Validar que se recibieron usuario y contraseña
if (!isset($data->username) || !isset($data->password)) {
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "Se requiere nombre de usuario y contraseña."));
    exit();
}

$username = $data->username;
$password = $data->password;

// Simulación de autenticación (en un caso real, consultarías la BD)
// Para esta demo, las credenciales son 'root' / 'root' como se especificó.
// En una aplicación real, NUNCA guardes contraseñas en texto plano. Usa hashes (ej. password_hash() y password_verify()).

if ($username === "root" && $password === "root") {
    // Credenciales correctas: establecer variables de sesión
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $username;
    // Podrías añadir un ID de usuario si lo tuvieras
    // $_SESSION['admin_user_id'] = 1; // Ejemplo

    http_response_code(200); // OK
    echo json_encode(array(
        "message" => "Inicio de sesión exitoso.",
        "username" => $username
        // "session_id" => session_id() // Opcional, para depuración
    ));
} else {
    // Credenciales incorrectas
    // Limpiar cualquier sesión previa por seguridad
    session_unset();
    session_destroy();

    http_response_code(401); // Unauthorized
    echo json_encode(array("message" => "Nombre de usuario o contraseña incorrectos."));
}
?>
