<?php
require 'conexion.php';

if (isset($_GET['nombre']) && isset($_GET['id'])) {
    $nombre = $_GET['nombre'];
    $id = $_GET['id'];

    $query = $pdo->prepare('SELECT imagen_nombre FROM incidencias WHERE id = :id');
    $query->bindParam(':id', $id);
    $query->execute();
    $incidencia = $query->fetch(PDO::FETCH_ASSOC);

    if ($incidencia) {
        $file_path = 'uploads/' . $incidencia['imagen_nombre'];

        if (file_exists($file_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        } else {
            echo 'Archivo no encontrado.';
        }
    } else {
        echo 'Incidencia no encontrada.';
    }
} else {
    echo 'Parámetros inválidos.';
}
?>
