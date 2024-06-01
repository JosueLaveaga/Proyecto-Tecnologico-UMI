<?php
session_start();
include 'conexion.php';

date_default_timezone_set('America/Mazatlan'); // Ajusta esto a tu zona horaria

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $hora_fin = date('Y-m-d H:i:s'); // Obtener la fecha y hora local en formato Y-m-d H:i:s

    $stmt = $pdo->prepare("UPDATE actividades SET hora_fin = ? WHERE id = ?");
    $stmt->execute([$hora_fin, $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}
echo json_encode(['success' => false]);
?>
