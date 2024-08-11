<?php
include 'conexion.php';

$data = json_decode(file_get_contents('php://input'), true);
$actividad_id = $data['actividad_id'];
$nuevo_user_id = $data['nuevo_user_id'];

// Agregar mensajes de depuración
error_log("Transferencia de actividad iniciada. ID de la actividad: $actividad_id, Nuevo usuario ID: $nuevo_user_id");

try {
    $stmt = $pdo->prepare("UPDATE actividades SET numero_empleado = :nuevo_user_id WHERE id = :actividad_id");
    $stmt->bindParam(':nuevo_user_id', $nuevo_user_id);
    $stmt->bindParam(':actividad_id', $actividad_id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        error_log("No se actualizó ninguna fila en la base de datos. Verificar IDs.");
        echo json_encode(['success' => false, 'message' => 'No se actualizó ninguna fila.']);
    }
} catch (Exception $e) {
    error_log("Error en la transferencia de actividad: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
