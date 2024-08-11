<?php
include 'conexion.php';

$id = $_GET['id'];
$fecha = date('Y-m-d H:i:s');

try {
    $stmt = $pdo->prepare("UPDATE actividades SET estado = 'completado', hora_fin = :hora_fin WHERE id = :id");
    $stmt->bindParam(':hora_fin', $fecha);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
