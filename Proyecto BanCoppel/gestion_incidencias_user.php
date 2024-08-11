<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'conexion.php';

$user_id = $_SESSION['user_id'];

$query = $pdo->prepare('SELECT numero_empleado, nombre, rol FROM usuarios WHERE numero_empleado = :id');
$query->bindParam(':id', $user_id);
$query->execute();

$user = $query->fetch(PDO::FETCH_ASSOC);

if ($user === false) {
    header("Location: login.php");
    exit();
}

// Establecer la zona horaria de Mazatlán, Culiacán, Sinaloa
date_default_timezone_set('America/Mazatlan');

$incidencia_registrada = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descripcion = $_POST['descripcion'];
    $prioridad = $_POST['prioridad'];
    $numero_empleado = $_SESSION['user_id'];
    $fecha = date('Y-m-d H:i:s');
    $estado = 'pendiente';

    $imagen = $_FILES['imagen'];
    $imagen_nombre = basename($imagen['name']);
    $imagen_tipo = $imagen['type'];
    $imagen_contenido = file_get_contents($imagen['tmp_name']);
    $imagen_base64 = base64_encode($imagen_contenido);

    // Mover el archivo a la carpeta de uploads
    $upload_dir = 'uploads/';
    $upload_file = $upload_dir . $imagen_nombre;
    if (move_uploaded_file($imagen['tmp_name'], $upload_file)) {
        try {
            $query = $pdo->prepare('INSERT INTO incidencias (numero_empleado, descripcion, imagen_nombre, imagen_tipo, imagen_contenido, prioridad, fecha, estado) VALUES (:numero_empleado, :descripcion, :imagen_nombre, :imagen_tipo, :imagen_contenido, :prioridad, :fecha, :estado)');
            $query->bindParam(':numero_empleado', $numero_empleado);
            $query->bindParam(':descripcion', $descripcion);
            $query->bindParam(':imagen_nombre', $imagen_nombre);
            $query->bindParam(':imagen_tipo', $imagen_tipo);
            $query->bindParam(':imagen_contenido', $imagen_base64, PDO::PARAM_LOB);
            $query->bindParam(':prioridad', $prioridad);
            $query->bindParam(':fecha', $fecha);
            $query->bindParam(':estado', $estado);
            $query->execute();
            $incidencia_registrada = true;
        } catch (PDOException $e) {
            echo "<script>alert('Error al registrar la incidencia: " . $e->getMessage() . "');</script>";
        }
    } else {
        echo "<script>alert('Error al cargar la imagen.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Incidencia</title>
    <style>
        /* El mismo CSS que ya has estado utilizando */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f4f4f4;
            overflow: hidden;
        }
        header {
            background-color: #0056b3;
            padding: 10px 20px;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            position: relative;
            z-index: 10;
        }
        .header-content {
            display: flex;
            align-items: center;
            width: 100%;
            max-width: 1200px;
            justify-content: space-between;
        }
        header img {
            height: 55px;
        }
        nav {
            flex: 1;
            text-align: right;
        }
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
        }
        nav ul li {
            margin: 0px 30%;
        }
        nav ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
        }
        .menu-toggle {
            display: none;
            font-size: 24px;
            cursor: pointer;
        }
        .mobile-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            padding: 20px;
            z-index: 1000;
        }
        .mobile-menu.active {
            display: block;
        }
        .mobile-menu ul {
            flex-direction: column;
            text-align: left;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .mobile-menu ul li {
            margin: 10px 0;
        }
        .mobile-menu ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
        }
        .container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            z-index: 10;
        }
        .report-panel {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .report-panel h1 {
            margin-bottom: 20px;
        }
        .report-panel form {
            display: flex;
            flex-direction: column;
        }
        .report-panel textarea,
        .report-panel select,
        .report-panel input[type="file"] {
            margin-bottom: 10px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .report-panel button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }
        .report-panel button:hover {
            background-color: #0056b3;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        }
        footer {
            text-align: center;
            padding: 10px;
            background-color: #0056b3;
            color: #fff;
            z-index: 10;
            position: relative;
        }
        @media (max-width: 768px) {
            nav ul {
                display: none;
            }
            .menu-toggle {
                display: block;
            }
            .mobile-menu ul {
                display: flex;
                flex-direction: column;
            }
            .mobile-menu ul li {
                margin: 10px 0;
            }
        }
        .background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            overflow: hidden;
        }
        .particle {
            position: absolute;
            width: 5px;
            height: 5px;
            background-color: #007bff;
            border-radius: 50%;
            opacity: 0.7;
            animation: drift 10s infinite linear;
        }
        @keyframes drift {
            from {
                transform: translateY(0) translateX(0);
            }
            to {
                transform: translateY(-100vh) translateX(50vw);
            }
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 300px;
            text-align: center;
        }
        .modal-content button {
            margin-top: 10px;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <img src="imagenes/coppel-logo2.png" alt="BanCoppel">
            <nav>
                <div class="menu-toggle" onclick="toggleMenu()">☰</div>
                <ul>
                    <li><a href="logout.php" class="logout-link">Cerrar Sesion</a></li>
                </ul>
            </nav>
        </div>
        <div class="mobile-menu" id="mobileMenu">
            <ul>
                <li><a href="index.php" onclick="toggleMenu()">Inicio</a></li>
                <li><a href="bitacora_actividades.php" onclick="toggleMenu()">Bitácora de Actividades</a></li>
                <li><a href="reporte.php" onclick="toggleMenu()">Reportes</a></li>
                <li><a href="gestion_incidencias_user.php" onclick="toggleMenu()">Incidencias</a></li>
                <li><a href="logout.php" onclick="toggleMenu()" class="logout-link">Cerrar Sesión</a></li>
            </ul>
        </div>
    </header>
    <div class="background" id="background"></div>
    <div class="container">
        <div class="report-panel">
            <h1>Reportar Incidencia</h1>
            <form method="post" enctype="multipart/form-data">
                <textarea name="descripcion" placeholder="Describe el problema..." required></textarea>
                <select name="prioridad" required>
                    <option value="alta">Alta</option>
                    <option value="media">Media</option>
                    <option value="baja">Baja</option>
                </select>
                <input type="file" name="imagen" accept="image/*" required>
                <button type="submit">Enviar Incidencia</button>
            </form>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 BanCoppel Derechos Reservados</p>
    </footer>

    <?php if ($incidencia_registrada): ?>
    <div id="modal" class="modal">
        <div class="modal-content">
            <p>Incidencia registrada exitosamente.</p>
            <button onclick="redirectToUserPanel()">Aceptar</button>
        </div>
    </div>
    <script>
        document.getElementById('modal').style.display = 'block';

        function redirectToUserPanel() {
            window.location.href = 'user_panel.php';
        }
    </script>
    <?php endif; ?>

    <script>
        function toggleMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('active');
        }

        // Crear partículas animadas
        function createParticle() {
            const particle = document.createElement('div');
            particle.classList.add('particle');
            particle.style.left = `${Math.random() * 100}%`;
            particle.style.top = '100%';
            particle.style.animationDuration = `${Math.random() * 10 + 5}s`;
            particle.style.opacity = Math.random();
            document.getElementById('background').appendChild(particle);

            // Eliminar la partícula después de que termine la animación
            particle.addEventListener('animationend', () => {
                particle.remove();
            });
        }

        // Crear múltiples partículas al cargar la página
        for (let i = 0; i < 100; i++) {
            setTimeout(createParticle, i * 100);
        }
    </script>
</body>
</html>
