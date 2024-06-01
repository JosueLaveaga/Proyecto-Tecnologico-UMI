<?php
session_start();
include 'conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    $id = $data['id'];
    $titulo = $data['titulo'];
    $descripcion = $data['descripcion'];
    $estado = $data['estado'];

    $stmt = $pdo->prepare("UPDATE actividades SET titulo = ?, descripcion = ?, estado = ? WHERE id = ?");
    $stmt->execute([$titulo, $descripcion, $estado, $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}
echo json_encode(['success' => false]);
?>
