<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['fecha'])) {
    $fecha = $data['fecha'];

    try {
        $stmt = $pdo->prepare("DELETE FROM actividades WHERE fecha = :fecha");
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Fecha no especificada.']);
}
?>
