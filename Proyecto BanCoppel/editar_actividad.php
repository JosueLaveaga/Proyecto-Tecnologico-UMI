<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'conexion.php';

$data = json_decode(file_get_contents("php://input"));

if (isset($data->id) && isset($data->titulo) && isset($data->descripcion) && isset($data->estado)) {
    $stmt = $pdo->prepare("UPDATE actividades SET titulo = :titulo, descripcion = :descripcion, estado = :estado WHERE id = :id");
    $stmt->bindParam(':id', $data->id);
    $stmt->bindParam(':titulo', $data->titulo);
    $stmt->bindParam(':descripcion', $data->descripcion);
    $stmt->bindParam(':estado', $data->estado);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
?>
