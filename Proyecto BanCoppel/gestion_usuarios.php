<?php
session_start();
if (!isset($_SESSION['user_rol']) || ($_SESSION['user_rol'] !== 'admin' && $_SESSION['user_rol'] !== 'user')) {
    echo "Acceso denegado.";
    exit;
}

include 'conexion.php';

// Obtener lista de usuarios
$stmt = $pdo->query("SELECT numero_empleado, nombre, email, rol FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <style>
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
        .user-table {
            width: 100%;
            max-width: 800px;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .user-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .user-table table, th, td {
            border: 1px solid #ddd;
        }
        .user-table th, .user-table td {
            padding: 10px;
            text-align: left;
        }
        .user-table th {
            background-color: #f4f4f4;
        }
        .actions a {
            margin-right: 5px;
            text-decoration: none;
            color: #007bff;
        }
        .actions a:hover {
            text-decoration: underline;
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
            z-index: 100;
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
            margin: 10px;
            padding: 10px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .modal-content button.cancel {
            background-color: #ccc;
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
                <li><a href="incidencias.php" onclick="toggleMenu()">Incidencias</a></li>
                <li><a href="login.php" onclick="toggleMenu()">Iniciar Sesión</a></li>
                <li><a href="registro.php" onclick="toggleMenu()">Registrarse</a></li>
            </ul>
        </div>
    </header>
    <div class="background" id="background"></div>
    <div class="container">
        <div class="user-table">
            <h2>Usuarios</h2>
            <table>
                <thead>
                    <tr>
                        <th>Número de Empleado</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['numero_empleado']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
                            <td class="actions">
                                <a href="editar_usuario.php?numero_empleado=<?php echo $usuario['numero_empleado']; ?>">✏️</a>
                                <a href="#" onclick="confirmDeletion(<?php echo $usuario['numero_empleado']; ?>)">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 BanCoppel Derechos Reservados</p>
    </footer>
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <p>¿Estás seguro que deseas eliminar este usuario?</p>
            <button id="confirmDeleteBtn">Eliminar</button>
            <button class="cancel" onclick="closeModal()">Cancelar</button>
        </div>
    </div>
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

        // Confirmación de eliminación
        let empleadoAEliminar;

        function confirmDeletion(numero_empleado) {
            empleadoAEliminar = numero_empleado;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        document.getElementById('confirmDeleteBtn').onclick = function () {
            window.location.href = 'eliminar_usuario.php?numero_empleado=' + empleadoAEliminar;
        }
    </script>
</body>
</html>
