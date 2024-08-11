<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include 'conexion.php';

// Obtener filtros de fecha y usuario
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');
$usuario = isset($_GET['usuario']) ? $_GET['usuario'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';

// Consulta a la base de datos para obtener las actividades dentro del rango de fechas y el usuario seleccionado
$query = "SELECT actividades.*, usuarios.nombre as usuario_nombre FROM actividades LEFT JOIN usuarios ON actividades.numero_empleado = usuarios.numero_empleado WHERE fecha BETWEEN :fecha_inicio AND :fecha_fin";
$params = [
    ':fecha_inicio' => $fecha_inicio,
    ':fecha_fin' => $fecha_fin
];

if ($usuario) {
    $query .= " AND usuarios.nombre = :usuario";
    $params[':usuario'] = $usuario;
}

if ($estado) {
    $query .= " AND actividades.estado = :estado";
    $params[':estado'] = $estado;
}

$query .= " ORDER BY fecha ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Definir el nombre del archivo
$filename = "reporte_actividades_" . date('Ymd') . ".csv";

// Establecer encabezados para la descarga del archivo
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Abrir la salida estándar como un archivo
$output = fopen('php://output', 'w');

// Escribir el BOM para asegurar la correcta visualización en Excel
fwrite($output, "\xEF\xBB\xBF");

// Escribir encabezados
fputcsv($output, ['Título', 'Descripción', 'Usuario', 'Fecha', 'Estado']);

// Escribir datos
foreach ($actividades as $actividad) {
    fputcsv($output, [$actividad['titulo'], $actividad['descripcion'], $actividad['usuario_nombre'], $actividad['fecha'], $actividad['estado']]);
}

// Cerrar el archivo de salida
fclose($output);
?>
