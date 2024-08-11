<?php
session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Establecer el encabezado de tipo de contenido para JSON
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents('php://input'), true);
    $titulo = $data['titulo'];
    $descripcion = $data['descripcion'];
    $estado = $data['estado'];
    $fecha = $data['fecha']; // Recibiendo la fecha desde el frontend
    $numero_empleado = $_SESSION['user_id']; // Suponiendo que el número de empleado está en la sesión

    // Modificar la consulta para utilizar la fecha recibida
    $stmt = $pdo->prepare("INSERT INTO actividades (titulo, descripcion, estado, numero_empleado, fecha) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$titulo, $descripcion, $estado, $numero_empleado, $fecha]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al insertar la actividad']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Método no permitido']);
?>
