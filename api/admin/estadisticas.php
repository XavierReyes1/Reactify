<?php
// api/admin/estadisticas.php

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

try {
    $estadisticas = array();

    // 1. Total de Respuestas
    $stmt_total = $db->query("SELECT COUNT(*) as total_respuestas FROM respuestas");
    $estadisticas['total_respuestas'] = (int) $stmt_total->fetchColumn();

    // 2. Satisfacción Promedio General
    $stmt_promedio = $db->query("SELECT AVG(nivel_satisfaccion) as satisfaccion_promedio FROM respuestas");
    $promedio_raw = $stmt_promedio->fetchColumn();
    $estadisticas['satisfaccion_promedio'] = $promedio_raw ? round((float)$promedio_raw, 2) : 0;

    // 3. Comentarios Negativos Hoy (puntuación <= 2)
    $hoy_inicio = date('Y-m-d 00:00:00');
    $hoy_fin = date('Y-m-d 23:59:59');
    $stmt_neg_hoy = $db->prepare("SELECT COUNT(*) as comentarios_negativos_hoy FROM respuestas WHERE nivel_satisfaccion <= 2 AND fecha BETWEEN :hoy_inicio AND :hoy_fin");
    $stmt_neg_hoy->bindParam(':hoy_inicio', $hoy_inicio);
    $stmt_neg_hoy->bindParam(':hoy_fin', $hoy_fin);
    $stmt_neg_hoy->execute();
    $estadisticas['comentarios_negativos_hoy'] = (int) $stmt_neg_hoy->fetchColumn();

    // 4. Tasa de Respuesta (Esto es más conceptual, ya que no tenemos "total de clientes atendidos" vs "respuestas")
    // Podríamos mostrar el número de respuestas en los últimos 7 días como una métrica de actividad.
    $hace_7_dias = date('Y-m-d 00:00:00', strtotime('-7 days'));
    $stmt_resp_7_dias = $db->prepare("SELECT COUNT(*) as respuestas_7_dias FROM respuestas WHERE fecha >= :hace_7_dias");
    $stmt_resp_7_dias->bindParam(':hace_7_dias', $hace_7_dias);
    $stmt_resp_7_dias->execute();
    $estadisticas['respuestas_ultimos_7_dias'] = (int) $stmt_resp_7_dias->fetchColumn();


    // Tendencias (simplificado, comparando con el día/semana anterior)
    // Trend Total Respuestas (vs ayer)
    $ayer_inicio = date('Y-m-d 00:00:00', strtotime('-1 day'));
    $ayer_fin = date('Y-m-d 23:59:59', strtotime('-1 day'));
    $stmt_total_hoy_count = $db->prepare("SELECT COUNT(*) FROM respuestas WHERE fecha BETWEEN :hoy_inicio AND :hoy_fin");
    $stmt_total_hoy_count->bindParam(':hoy_inicio', $hoy_inicio);
    $stmt_total_hoy_count->bindParam(':hoy_fin', $hoy_fin);
    $stmt_total_hoy_count->execute();
    $total_hoy = (int)$stmt_total_hoy_count->fetchColumn();

    $stmt_total_ayer_count = $db->prepare("SELECT COUNT(*) FROM respuestas WHERE fecha BETWEEN :ayer_inicio AND :ayer_fin");
    $stmt_total_ayer_count->bindParam(':ayer_inicio', $ayer_inicio);
    $stmt_total_ayer_count->bindParam(':ayer_fin', $ayer_fin);
    $stmt_total_ayer_count->execute();
    $total_ayer = (int)$stmt_total_ayer_count->fetchColumn();
    
    $estadisticas['trend_respuestas_vs_ayer'] = ($total_ayer > 0) ? round((($total_hoy - $total_ayer) / $total_ayer) * 100, 1) . "%" : ($total_hoy > 0 ? "+100%" : "0%");


    http_response_code(200);
    echo json_encode($estadisticas);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Error al obtener estadísticas: " . $e->getMessage()));
}
?>
