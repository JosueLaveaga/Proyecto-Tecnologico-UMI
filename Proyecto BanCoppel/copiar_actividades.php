<?php
include 'conexion.php';

// Obtener datos del POST
$data = json_decode(file_get_contents('php://input'), true);
$fromDate = $data['fromDate'];
$toDate = $data['toDate'];

try {
    // Iniciar una transacción
    $pdo->beginTransaction();

    // Obtener actividades de la fecha origen
    $stmt = $pdo->prepare("SELECT * FROM actividades WHERE fecha = :fromDate");
    $stmt->bindParam(':fromDate', $fromDate);
    $stmt->execute();
    $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Preparar las consultas
    $stmt_check = $pdo->prepare("SELECT * FROM actividades WHERE fecha = :toDate AND titulo = :titulo AND descripcion = :descripcion AND hora_inicio = :hora_inicio AND hora_fin = :hora_fin AND numero_empleado = :numero_empleado");
    $stmt_insert = $pdo->prepare("INSERT INTO actividades (titulo, descripcion, estado, fecha, hora_inicio, hora_fin, numero_empleado) VALUES (:titulo, :descripcion, :estado, :fecha, :hora_inicio, :hora_fin, :numero_empleado)");

    foreach ($actividades as $actividad) {
        // Comprobar si la actividad ya existe en la fecha destino
        $stmt_check->bindParam(':toDate', $toDate);
        $stmt_check->bindParam(':titulo', $actividad['titulo']);
        $stmt_check->bindParam(':descripcion', $actividad['descripcion']);
        $stmt_check->bindParam(':hora_inicio', $actividad['hora_inicio']);
        $stmt_check->bindParam(':hora_fin', $actividad['hora_fin']);
        $stmt_check->bindParam(':numero_empleado', $actividad['numero_empleado']);
        $stmt_check->execute();

        if ($stmt_check->rowCount() === 0) {
            // Insertar actividad si no existe en la fecha destino
            $stmt_insert->bindParam(':titulo', $actividad['titulo']);
            $stmt_insert->bindParam(':descripcion', $actividad['descripcion']);
            // Establecer el estado como 'pendiente' sin importar el estado original
            $estado = 'pendiente';
            $stmt_insert->bindParam(':estado', $estado);
            $stmt_insert->bindParam(':fecha', $toDate);
            $stmt_insert->bindParam(':hora_inicio', $actividad['hora_inicio']);
            $stmt_insert->bindParam(':hora_fin', $actividad['hora_fin']);
            $stmt_insert->bindParam(':numero_empleado', $actividad['numero_empleado']);
            $stmt_insert->execute();
        }
    }

    // Confirmar la transacción
    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
