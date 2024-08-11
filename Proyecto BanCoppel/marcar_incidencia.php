<?php
session_start();
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

require 'conexion.php';

$id = $_GET['id'];
$estado = $_GET['estado'];

if (!in_array($estado, ['pendiente', 'en_proceso', 'resuelta'])) {
    echo json_encode(['success' => false, 'message' => 'Estado inválido.']);
    exit;
}

try {
    $hora_actualizacion = date('Y-m-d H:i:s'); // Obtener la fecha y hora actual

    $query = $pdo->prepare('UPDATE incidencias SET estado = :estado, hora_actualizacion = :hora_actualizacion WHERE id = :id');
    $query->bindParam(':estado', $estado);
    $query->bindParam(':hora_actualizacion', $hora_actualizacion);
    $query->bindParam(':id', $id);
    $query->execute();

    if($query->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Incidencia actualizada correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró la incidencia o no se actualizó.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la incidencia: ' . $e->getMessage()]);
}
?>
