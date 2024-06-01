<?php
session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $titulo = $data['titulo'];
    $descripcion = $data['descripcion'];
    $estado = $data['estado'];
    $numero_empleado = $_SESSION['user_id']; // Suponiendo que el número de empleado está en la sesión

    $stmt = $pdo->prepare("INSERT INTO actividades (titulo, descripcion, estado, numero_empleado, fecha) VALUES (?, ?, ?, ?, CURRENT_DATE)");
    $stmt->execute([$titulo, $descripcion, $estado, $numero_empleado]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}
echo json_encode(['success' => false]);
?>
