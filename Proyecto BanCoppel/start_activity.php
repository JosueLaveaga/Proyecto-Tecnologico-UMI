<?php
include 'conexion.php';

$id = $_GET['id'];
$fecha = date('Y-m-d H:i:s');

try {
    $stmt = $pdo->prepare("UPDATE actividades SET estado = 'activo', hora_inicio = :hora_inicio WHERE id = :id");
    $stmt->bindParam(':hora_inicio', $fecha);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
