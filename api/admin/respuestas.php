<?php
// api/admin/respuestas.php

include_once '../../includes/database.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(array("message" => "Acceso no autorizado."));
    exit();
}

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

if ($db === null) {
    exit();
}

// Parámetros de paginación y filtrado (desde query string GET)
$pagina = isset($_GET['pagina']) ? filter_var($_GET['pagina'], FILTER_VALIDATE_INT, ["options" => ["default" => 1, "min_range" => 1]]) : 1;
$limit = isset($_GET['limit']) ? filter_var($_GET['limit'], FILTER_VALIDATE_INT, ["options" => ["default" => 10, "min_range" => 1]]) : 10;
$offset = ($pagina - 1) * $limit;

// Filtros
$area_id_filtro = isset($_GET['area_id']) && $_GET['area_id'] !== 'all' ? filter_var($_GET['area_id'], FILTER_VALIDATE_INT) : null;
$fecha_desde_filtro = isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde']) ? $_GET['fecha_desde'] . ' 00:00:00' : null;
$fecha_hasta_filtro = isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] . ' 23:59:59' : null;
$calificacion_filtro = isset($_GET['calificacion']) && $_GET['calificacion'] !== 'all' ? filter_var($_GET['calificacion'], FILTER_VALIDATE_INT) : null;
$keyword_comentario_filtro = isset($_GET['keyword']) && !empty($_GET['keyword']) ? htmlspecialchars(strip_tags(trim($_GET['keyword']))) : null;

// Construir la consulta base
$query_base = "FROM respuestas r LEFT JOIN areas a ON r.areas_id = a.id";
$where_clauses = array();
$params = array();

if ($area_id_filtro !== null && $area_id_filtro !== false) {
    $where_clauses[] = "r.areas_id = :area_id";
    $params[':area_id'] = $area_id_filtro;
}
if ($fecha_desde_filtro !== null) {
    $where_clauses[] = "r.fecha >= :fecha_desde";
    $params[':fecha_desde'] = $fecha_desde_filtro;
}
if ($fecha_hasta_filtro !== null) {
    $where_clauses[] = "r.fecha <= :fecha_hasta";
    $params[':fecha_hasta'] = $fecha_hasta_filtro;
}
if ($calificacion_filtro !== null && $calificacion_filtro !== false) {
    $where_clauses[] = "r.nivel_satisfaccion = :calificacion";
    $params[':calificacion'] = $calificacion_filtro;
}
if ($keyword_comentario_filtro !== null) {
    $where_clauses[] = "r.comentario LIKE :keyword";
    $params[':keyword'] = "%" . $keyword_comentario_filtro . "%";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

try {
    // Consulta para contar el total de registros (con filtros)
    $count_query = "SELECT COUNT(r.id) as total_registros " . $query_base . $where_sql;
    $stmt_count = $db->prepare($count_query);
    $stmt_count->execute($params);
    $total_registros = (int) $stmt_count->fetchColumn();
    $total_paginas = ceil($total_registros / $limit);

    // Consulta para obtener los datos paginados
    $data_query = "SELECT r.id, r.fecha, r.nivel_satisfaccion, r.comentario, a.nombre as nombre_area "
                . $query_base . $where_sql
                . " ORDER BY r.fecha DESC LIMIT :limit OFFSET :offset";

    $stmt_data = $db->prepare($data_query);
    // Vincular parámetros de filtros
    foreach ($params as $key => &$val) { // Pasar por referencia para bindParam
        $stmt_data->bindParam($key, $val);
    }
    // Vincular parámetros de paginación (asegurarse que son enteros)
    $stmt_data->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt_data->bindParam(':offset', $offset, PDO::PARAM_INT);

    $stmt_data->execute();
    $respuestas = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

    // Obtener aspectos para cada respuesta (esto puede ser N+1, optimizar si es necesario para alto volumen)
    $respuestas_con_aspectos = array();
    if (count($respuestas) > 0) {
        $ids_respuestas = array_column($respuestas, 'id');
        $placeholders = implode(',', array_fill(0, count($ids_respuestas), '?'));
        
        $aspectos_query = "SELECT respuesta_id, aspecto FROM aspectos_respuesta WHERE respuesta_id IN ($placeholders)";
        $stmt_aspectos = $db->prepare($aspectos_query);
        $stmt_aspectos->execute($ids_respuestas);
        $aspectos_raw = $stmt_aspectos->fetchAll(PDO::FETCH_ASSOC);

        $aspectos_por_respuesta = array();
        foreach ($aspectos_raw as $ar) {
            $aspectos_por_respuesta[$ar['respuesta_id']][] = $ar['aspecto'];
        }

        foreach ($respuestas as $respuesta) {
            $respuesta['aspectos'] = isset($aspectos_por_respuesta[$respuesta['id']]) ? $aspectos_por_respuesta[$respuesta['id']] : array();
            $respuestas_con_aspectos[] = $respuesta;
        }
    }


    http_response_code(200);
    echo json_encode(array(
        "pagina" => $pagina,
        "limit" => $limit,
        "total_registros" => $total_registros,
        "total_paginas" => $total_paginas,
        "data" => $respuestas_con_aspectos
    ));

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Error al obtener respuestas: " . $e->getMessage()));
}
?>
