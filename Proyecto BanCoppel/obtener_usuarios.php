<?php
include 'conexion.php';

$stmt = $pdo->prepare("SELECT numero_empleado, nombre FROM usuarios");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['usuarios' => $usuarios]);
