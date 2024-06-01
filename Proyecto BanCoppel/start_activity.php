<?php
session_start();
include 'conexion.php';

date_default_timezone_set('America/Mazatlan'); // Ajusta esto a tu zona horaria

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $hora_inicio = date('Y-m-d H:i:s'); // Obtener la fecha y hora local en formato Y-m-d H:i:s

    $stmt = $pdo->prepare("UPDATE actividades SET hora_inicio = ? WHERE id = ?");
    $stmt->execute([$hora_inicio, $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}
echo json_encode(['success' => false]);
?>
