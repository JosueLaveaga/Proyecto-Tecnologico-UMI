<?php
// index.php
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma de Gestión de Procesamiento De Información</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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
        }
        .hero {
            position: relative;
            text-align: center;
            padding: 50px 20px;
            background: url('imagenes/hero-bg.jpg') no-repeat center center;
            background-size: cover;
            color: #fff;
            height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }
        .hero h1, .hero p, .hero .btn {
            position: relative;
            z-index: 2;
        }
        .hero h1 {
            font-size: 36px;
            margin-bottom: 20px;
        }
        .hero p {
            font-size: 18px;
            margin-bottom: 30px;
        }
        .hero .btn {
            background-color: #ffc107;
            color: #000;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 18px;
            border-radius: 5px;
        }
        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            padding: 50px 20px;
        }
        .features div {
            max-width: 300px;
            text-align: center;
            margin: 10px;
        }
        .features h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .features p {
            font-size: 16px;
        }
        footer {
            text-align: center;
            padding: 10px;
            background-color: #0056b3;
            color: #fff;
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
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <img src="imagenes/coppel-logo2.png" alt="BanCoppel">
            <nav>
                <div class="menu-toggle" onclick="toggleMenu()">☰</div>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="bitacora_actividades.php">Bitácora de Actividades</a></li>
                    <li><a href="reporte.php">Reportes</a></li>
                    <li><a href="incidencias.php">Incidencias</a></li>
                    <li><a href="login.php">Iniciar Sesión</a></li>
                    <li><a href="registro.php">Registrarse</a></li>
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
    <div class="container">
        <main>
            <section class="hero">
                <h1>Bienvenido a la Plataforma de Gestión de Procesamiento De Información</h1>
                <p>Gestione sus actividades y mantenga un registro eficiente con nuestra herramienta.</p>
                <a href="login.php" class="btn">Comenzar</a> <!-- Redirige a login.php -->
            </section>
            <section class="features">
                <div>
                    <h2>Gestión de Actividades</h2>
                    <p>Registre y monitoree todas las actividades de su equipo.</p>
                </div>
                <div>
                    <h2>Reportes en Tiempo Real</h2>
                    <p>Genere reportes detallados en formato Excel.</p>
                </div>
                <div>
                    <h2>Atención a Incidencias</h2>
                    <p>Reporte y gestione incidencias de manera eficiente.</p>
                </div>
            </section>
        </main>
    </div>
    <footer>
        <p>&copy; 2024 BanCoppel Derechos Reservados</p>
    </footer>
    <script>
        function toggleMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('active');
        }
    </script>
</body>
</html>
