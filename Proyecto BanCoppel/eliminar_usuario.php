<?php
session_start();
if (!isset($_SESSION['user_rol']) || ($_SESSION['user_rol'] !== 'admin' && $_SESSION['user_rol'] !== 'user')) {
    echo "Acceso denegado.";
    exit();
}

include 'conexion.php';

if (isset($_GET['numero_empleado'])) {
    $numero_empleado = $_GET['numero_empleado'];

    // Verificar que el usuario no se esté eliminando a sí mismo
    if ($numero_empleado == $_SESSION['user_id']) {
        echo "No puedes eliminarte a ti mismo.";
        exit();
    }

    // Eliminar el usuario
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE numero_empleado = ?");
    $stmt->execute([$numero_empleado]);

    header('Location: gestion_usuarios.php');
    exit();
} else {
    echo "Número de empleado no especificado.";
    exit();
}
?>
