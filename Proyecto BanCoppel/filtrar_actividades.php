<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'conexion.php';

$fecha = $_GET['fecha'];

// Obtener actividades filtradas por fecha
$stmt = $pdo->prepare("SELECT actividades.*, TO_CHAR(actividades.hora_inicio, 'HH24:MI:SS') as hora_inicio_formateada, TO_CHAR(actividades.hora_fin, 'HH24:MI:SS') as hora_fin_formateada, usuarios.nombre as usuario_nombre FROM actividades LEFT JOIN usuarios ON actividades.numero_empleado = usuarios.numero_empleado WHERE DATE(fecha) = :fecha ORDER BY CASE WHEN estado = 'completado' THEN 1 ELSE 2 END, fecha DESC");
$stmt->execute(['fecha' => $fecha]);
$actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['actividades' => $actividades]);
?>
