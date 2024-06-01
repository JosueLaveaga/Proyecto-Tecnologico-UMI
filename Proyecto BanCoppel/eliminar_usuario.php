<?php
session_start();
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
    echo "Acceso denegado.";
    exit;
}

include 'conexion.php';

if (isset($_GET['numero_empleado'])) {
    $numero_empleado = $_GET['numero_empleado'];

    // Eliminar el usuario de la base de datos
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE numero_empleado = ?");
    $stmt->execute([$numero_empleado]);

    // Redireccionar de vuelta a la página de gestión de usuarios
    header('Location: gestion_usuarios.php');
    exit;
} else {
    echo "Número de empleado no proporcionado.";
    exit;
}
?>
