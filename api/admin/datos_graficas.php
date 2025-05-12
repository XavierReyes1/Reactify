<?php
// api/admin/datos_graficas.php

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

// Parámetros de filtrado (opcionales, pueden venir de GET)
$area_id_filtro = isset($_GET['area_id']) && $_GET['area_id'] !== 'all' ? filter_var($_GET['area_id'], FILTER_VALIDATE_INT) : null;
$fecha_desde_filtro = isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null; // Formato YYYY-MM-DD
$fecha_hasta_filtro = isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null; // Formato YYYY-MM-DD

$tipo_grafica = isset($_GET['tipo']) ? $_GET['tipo'] : 'satisfaccion_diaria'; // Por defecto

$where_clauses = array("1=1"); // Cláusula base
$params = array();

if ($area_id_filtro !== null && $area_id_filtro !== false) {
    $where_clauses[] = "r.areas_id = :area_id";
    $params[':area_id'] = $area_id_filtro;
}
if ($fecha_desde_filtro !== null) {
    $where_clauses[] = "DATE(r.fecha) >= :fecha_desde";
    $params[':fecha_desde'] = $fecha_desde_filtro;
}
if ($fecha_hasta_filtro !== null) {
    $where_clauses[] = "DATE(r.fecha) <= :fecha_hasta";
    $params[':fecha_hasta'] = $fecha_hasta_filtro;
}
$where_sql = implode(" AND ", $where_clauses);


try {
    $datos_grafica = array(
        'labels' => [],
        'datasets' => []
    );

    if ($tipo_grafica === 'satisfaccion_diaria') {
        // Agrupar por día y calcular promedio de satisfacción
        // Para simplificar, tomaremos los últimos 30 días si no hay filtro de fecha
        if ($fecha_desde_filtro === null && $fecha_hasta_filtro === null) {
             $where_clauses[] = "r.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
             $where_sql = implode(" AND ", $where_clauses); // Reconstruir where_sql
        }

        $query = "SELECT DATE_FORMAT(r.fecha, '%Y-%m-%d') as dia, AVG(r.nivel_satisfaccion) as promedio_satisfaccion
                  FROM respuestas r
                  WHERE $where_sql
                  GROUP BY dia
                  ORDER BY dia ASC";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $labels = array();
        $data_points = array();
        foreach ($resultados as $row) {
            $labels[] = $row['dia'];
            $data_points[] = round((float)$row['promedio_satisfaccion'], 2);
        }
        $datos_grafica['labels'] = $labels;
        $datos_grafica['datasets'][] = array(
            'label' => 'Satisfacción Promedio Diaria',
            'data' => $data_points,
            'borderColor' => 'rgb(75, 192, 192)',
            'tension' => 0.1
        );

    } elseif ($tipo_grafica === 'distribucion_calificaciones') {
        $query = "SELECT nivel_satisfaccion, COUNT(*) as cantidad
                  FROM respuestas r
                  WHERE $where_sql
                  GROUP BY nivel_satisfaccion
                  ORDER BY nivel_satisfaccion ASC";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $labels = array('1 Estrella', '2 Estrellas', '3 Estrellas', '4 Estrellas', '5 Estrellas');
        $data_points = array_fill(0, 5, 0); // Inicializar con ceros

        foreach ($resultados as $row) {
            if ($row['nivel_satisfaccion'] >= 1 && $row['nivel_satisfaccion'] <= 5) {
                $data_points[$row['nivel_satisfaccion'] - 1] = (int)$row['cantidad'];
            }
        }
        $datos_grafica['labels'] = $labels;
        $datos_grafica['datasets'][] = array(
            'label' => 'Distribución de Calificaciones',
            'data' => $data_points,
            'backgroundColor' => ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#0d6efd']
        );
    } elseif ($tipo_grafica === 'frecuencia_aspectos') {
        // Obtener los N aspectos más mencionados
        $limit_aspectos = isset($_GET['limit_aspectos']) ? (int)$_GET['limit_aspectos'] : 10;
        $query = "SELECT aspecto, COUNT(*) as cantidad
                  FROM aspectos_respuesta ar
                  JOIN respuestas r ON ar.respuesta_id = r.id
                  WHERE $where_sql -- Aplicar filtros también a las respuestas de donde vienen los aspectos
                  GROUP BY aspecto
                  ORDER BY cantidad DESC
                  LIMIT :limit_aspectos";
        $stmt = $db->prepare($query);
         // Vincular parámetros de filtros (si los hay)
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->bindParam(':limit_aspectos', $limit_aspectos, PDO::PARAM_INT);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $labels = array();
        $data_points = array();
        foreach ($resultados as $row) {
            $labels[] = $row['aspecto'];
            $data_points[] = (int)$row['cantidad'];
        }
        $datos_grafica['labels'] = $labels;
        $datos_grafica['datasets'][] = array(
            'label' => 'Frecuencia de Aspectos',
            'data' => $data_points,
            'backgroundColor' => 'rgba(54, 162, 235, 0.6)' // Azul
        );
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Tipo de gráfica no soportado."));
        exit();
    }

    http_response_code(200);
    echo json_encode($datos_grafica);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Error al obtener datos para gráficas: " . $e->getMessage()));
}
?>
