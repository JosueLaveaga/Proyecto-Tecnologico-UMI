<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'conexion.php';

// Obtener datos del usuario
if (isset($_GET['numero_empleado'])) {
    $numero_empleado = $_GET['numero_empleado'];
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE numero_empleado = ?");
    $stmt->execute([$numero_empleado]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Actualizar datos del usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_empleado = $_POST['numero_empleado'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $rol = $_POST['rol'];
    $old_numero_empleado = $_POST['old_numero_empleado'];

    // Validaciones en el backend
    if (!preg_match('/^\d{1,8}$/', $numero_empleado)) {
        $error_message = "El número de empleado debe tener hasta 8 dígitos y solo puede contener números.";
    } elseif (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{8,}$/', $password)) {
        $error_message = "La contraseña debe tener al menos un carácter especial, un número, letras minúsculas y mayúsculas, y una longitud máxima de 8 caracteres.";
    } else {
        $stmt = $pdo->prepare("UPDATE usuarios SET numero_empleado = ?, nombre = ?, email = ?, password = ?, rol = ? WHERE numero_empleado = ?");
        $stmt->execute([$numero_empleado, $nombre, $email, $password, $rol, $old_numero_empleado]);

        header('Location: gestion_usuarios.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
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
            margin: 0 15px;
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
        .edit-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .edit-container h1 {
            margin-bottom: 20px;
        }
        .edit-container form {
            display: flex;
            flex-direction: column;
        }
        .edit-container input, .edit-container select {
            margin-bottom: 10px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .edit-container button {
            padding: 10px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }
        .edit-container button:hover {
            background-color: #0056b3;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        }
        .error {
            color: red;
            margin-bottom: 10px;
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
                    <li><a href="logout.php">Cerrar sesión</a></li>
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
        <div class="edit-container">
            <h1>Editar Usuario</h1>
            <?php if (isset($error_message)): ?>
                <p class="error"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <form method="post" action="editar_usuario.php" onsubmit="return validarFormulario()">
                <input type="hidden" name="old_numero_empleado" value="<?php echo htmlspecialchars($usuario['numero_empleado']); ?>">
                <input type="text" id="numero_empleado" name="numero_empleado" value="<?php echo htmlspecialchars($usuario['numero_empleado']); ?>" maxlength="8" placeholder="Número de Empleado" required>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" placeholder="Nombre" required>
                <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" placeholder="Email" required>
                <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($usuario['password']); ?>" placeholder="Contraseña" required>
                <select name="rol" required>
                    <option value="admin" <?php echo $usuario['rol'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="user" <?php echo $usuario['rol'] === 'user' ? 'selected' : ''; ?>>Usuario</option>
                </select>
                <button type="submit">Guardar Cambios</button>
            </form>
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

        function validarFormulario() {
            const numero_empleado = document.getElementById('numero_empleado').value;
            const password = document.getElementById('password').value;
            const error_message = document.getElementById('error-message');

            // Validar número de empleado
            const numeroEmpleadoRegex = /^\d{1,8}$/;
            if (!numeroEmpleadoRegex.test(numero_empleado)) {
                error_message.textContent = 'El número de empleado debe tener hasta 8 dígitos y solo puede contener números.';
                return false;
            }

            // Validar contraseña
            const passwordRegex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{8,}$/;
            if (!passwordRegex.test(password)) {
                error_message.textContent = 'La contraseña debe tener al menos un carácter especial, un número, letras minúsculas y mayúsculas, y una longitud máxima de 8 caracteres.';
                return false;
            }

            return true;
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
