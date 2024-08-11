<?php
session_start();
include 'conexion.php';

// Verificar si hay incidencias resueltas y no notificadas para el usuario actual
$stmt = $pdo->prepare("
    SELECT id, descripcion
    FROM incidencias
    WHERE numero_empleado = :user_id 
    AND estado = 'resuelta' 
    AND notificado = FALSE
");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$incidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si hay incidencias resueltas no notificadas
if (count($incidencias) > 0) {
    // Marcar estas incidencias como notificadas
    $ids = array_column($incidencias, 'id');
    $ids_placeholder = implode(',', array_fill(0, count($ids), '?'));
    $stmt_update = $pdo->prepare("UPDATE incidencias SET notificado = TRUE WHERE id IN ($ids_placeholder)");
    $stmt_update->execute($ids);

    echo json_encode(['resuelta' => true, 'incidencias' => $incidencias]);
} else {
    echo json_encode(['resuelta' => false]);
}
?>
