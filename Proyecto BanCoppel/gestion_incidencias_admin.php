<?php
session_start();
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
    echo "Acceso denegado.";
    exit;
}

require 'conexion.php';

// Obtener las incidencias pendientes y en proceso
$query = $pdo->prepare("SELECT i.*, u.nombre AS nombre_colaborador FROM incidencias i JOIN usuarios u ON i.numero_empleado = u.numero_empleado WHERE i.estado IN ('pendiente', 'en_proceso') ORDER BY i.estado, i.fecha DESC");
$query->execute();
$incidencias = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Incidencias</title>
    <style>
        /* CSS personalizado */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f4f4f4;
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
        .admin-panel {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            text-align: center;
        }
        .admin-panel h1 {
            margin-bottom: 20px;
        }
        .admin-panel .incidencia {
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            text-align: left;
        }
        .admin-panel .incidencia img {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
        }
        .boton-accion {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }
        .boton-accion:hover {
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
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <img src="imagenes/coppel-logo2.png" alt="BanCoppel">
            <nav>
                <div class="menu-toggle" onclick="toggleMenu()">☰</div>
                <ul>
                    <li><a href="logout.php">Cerrar Sesion</a></li>
                </ul>
            </nav>
        </div>
        <div class="mobile-menu" id="mobileMenu">
            <ul>
                <li><a href="index.php" onclick="toggleMenu()">Inicio</a></li>
                <li><a href="bitacora_actividades.php" onclick="toggleMenu()">Bitácora de Actividades</a></li>
                <li><a href="reporte.php" onclick="toggleMenu()">Reportes</a></li>
                <li><a href="gestion_incidencias_admin.php" onclick="toggleMenu()">Incidencias</a></li>
                <li><a href="logout.php" onclick="toggleMenu()">Cerrar Sesión</a></li>
            </ul>
        </div>
    </header>
    <div class="background" id="background"></div>
    <div class="container">
        <div class="admin-panel">
            <h1>Gestión de Incidencias</h1>
            <?php if (count($incidencias) > 0): ?>
                <?php foreach ($incidencias as $incidencia): ?>
                    <div class="incidencia">
                        <p><strong>Fecha:</strong> <?php echo htmlspecialchars($incidencia['fecha']); ?></p>
                        <p><strong>Hora de Última Actualización:</strong> <?php echo htmlspecialchars($incidencia['hora_actualizacion'] ?? ''); ?></p>
                        <p><strong>Descripción:</strong> <?php echo htmlspecialchars($incidencia['descripcion']); ?></p>
                        <p><strong>Prioridad:</strong> <?php echo htmlspecialchars($incidencia['prioridad']); ?></p>
                        <p><strong>Colaborador:</strong> <?php echo htmlspecialchars($incidencia['nombre_colaborador']); ?></p>
                        <?php if (!empty($incidencia['imagen_nombre'])): ?>
                            <button class="boton-accion" onclick="window.location.href='descargar_imagen.php?nombre=<?php echo htmlspecialchars($incidencia['imagen_nombre']); ?>&id=<?php echo $incidencia['id']; ?>'">Descargar Imagen</button>
                        <?php endif; ?>
                        <button class="boton-accion" onclick="marcarEnProceso(<?php echo $incidencia['id']; ?>)">Marcar como En Proceso</button>
                        <button class="boton-accion" onclick="marcarResuelta(<?php echo $incidencia['id']; ?>)">Marcar como Resuelta</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay incidencias pendientes o en proceso.</p>
            <?php endif; ?>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 BanCoppel Derechos Reservados</p>
    </footer>
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

        // Funciones para marcar incidencia
        function marcarEnProceso(id) {
            fetch('marcar_incidencia.php?id=' + id + '&estado=en_proceso')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error al marcar la incidencia como en proceso');
                    }
                });
        }

        function marcarResuelta(id) {
            fetch('marcar_incidencia.php?id=' + id + '&estado=resuelta')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error al marcar la incidencia como resuelta');
                    }
                });
        }
    </script>
</body>
</html>
