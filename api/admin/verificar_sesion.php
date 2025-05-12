<?php
// api/admin/verificar_sesion.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    http_response_code(200);
    echo json_encode(array(
        "loggedIn" => true,
        "username" => isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : null
        // "userId" => isset($_SESSION['admin_user_id']) ? $_SESSION['admin_user_id'] : null
    ));
} else {
    http_response_code(401); // No autorizado
    echo json_encode(array("loggedIn" => false, "message" => "No hay sesión activa."));
}
?>
