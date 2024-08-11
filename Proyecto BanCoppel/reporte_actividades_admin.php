<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'conexion.php';

// Obtener filtros de fecha
$fecha_inicio = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Consulta a la base de datos para obtener las actividades dentro del rango de fechas seleccionado
$queryActividades = "SELECT actividades.*, usuarios.nombre as usuario_nombre 
                     FROM actividades 
                     LEFT JOIN usuarios ON actividades.numero_empleado = usuarios.numero_empleado 
                     WHERE fecha = :fecha 
                     ORDER BY fecha ASC";
$params = [
    ':fecha' => $fecha_inicio
];

$stmtActividades = $pdo->prepare($queryActividades);
$stmtActividades->execute($params);
$actividades = $stmtActividades->fetchAll(PDO::FETCH_ASSOC);

// Preparar datos para gráficos
$actividades_por_estado = [
    'pendiente' => 0,
    'activo' => 0,
    'completado' => 0
];

$actividades_por_usuario_y_estado = [];

foreach ($actividades as $actividad) {
    $estado = $actividad['estado'];
    $usuario_nombre = $actividad['usuario_nombre'];

    if (!isset($actividades_por_usuario_y_estado[$usuario_nombre])) {
        $actividades_por_usuario_y_estado[$usuario_nombre] = [
            'pendiente' => 0,
            'activo' => 0,
            'completado' => 0
        ];
    }

    $actividades_por_estado[$estado]++;
    $actividades_por_usuario_y_estado[$usuario_nombre][$estado]++;
}

// Convertir datos a formato JSON para JavaScript
$actividades_por_estado_json = json_encode($actividades_por_estado);
$actividades_por_usuario_y_estado_json = json_encode($actividades_por_usuario_y_estado);

// Exportar actividades a CSV
if (isset($_GET['tipo']) && $_GET['tipo'] === 'actividades') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=actividades_reporte.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Actividad', 'Usuario', 'Fecha']);

    $query = $pdo->prepare("
        SELECT a.titulo AS actividad, u.nombre, a.fecha
        FROM actividades a
        JOIN usuarios u ON a.numero_empleado = u.numero_empleado
        WHERE a.fecha = :fecha
    ");
    $query->execute($params);

    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Actividades</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
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
    padding: 20px;
    text-align: center;
    flex: 1;
}

.chart-container {
    width: 35%;
    margin: 70px auto;
    height: 300px;
}

.download-button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 5px;
    margin-top: 20px;
}

.download-button:hover {
    background-color: #0056b3;
}

table {
    width: 40%;
    margin: 10px auto;
    border-collapse: collapse;
}

table th, table td {
    border: 1px solid #ddd;
    padding: 8px;
}

table th {
    background-color: #87CEEB;
    color: white;
}

table tr:nth-child(even) {
    background-color: #f2f2f2;
}

footer {
    background-color: #0056b3;
    color: #fff;
    padding: 10px 20px;
    text-align: center;
    width: 100%;
    margin-top: auto;
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
        <h2>Filtrar por Fecha</h2>
        <form method="get" action="reporte_actividades_admin.php">
            <label for="fecha">Fecha:</label>
            <input type="date" id="fecha" name="fecha" value="<?php echo htmlspecialchars($fecha_inicio); ?>" required>
            <button type="submit">Filtrar</button>
        </form>
        <h2>Estado de Actividades</h2>
        <div class="chart-container">
            <canvas id="actividadesChart"></canvas>
        </div>
        <h2>Detalles de Actividades</h2>
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Pendientes</th>
                    <th>Activas</th>
                    <th>Completadas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($actividades_por_usuario_y_estado as $usuario => $estados): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario); ?></td>
                        <td><?php echo htmlspecialchars($estados['pendiente']); ?></td>
                        <td><?php echo htmlspecialchars($estados['activo']); ?></td>
                        <td><?php echo htmlspecialchars($estados['completado']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button class="download-button" onclick="descargarCSV('actividades')">Descargar Actividades CSV</button>
    </div>
    <footer>
        <p>&copy; 2024 BanCoppel Derechos Reservados</p>
    </footer>
    <script>
        const ctxActividades = document.getElementById('actividadesChart').getContext('2d');

        const actividadesChart = new Chart(ctxActividades, {
            type: 'bar',
            data: {
                labels: ['Pendiente', 'Activo', 'Completado'],
                datasets: [{
                    label: 'Actividades',
                    data: Object.values(<?php echo $actividades_por_estado_json; ?>),
                    backgroundColor: ['#ff6384', '#36a2eb', '#cc65fe']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Estado de Actividades'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1 // Mostrar solo números enteros
                        }
                    }
                }
            }
        });

        function descargarCSV(tipo) {
            const params = new URLSearchParams(window.location.search);
            params.set('tipo', tipo);
            window.location.href = `reporte_actividades_admin.php?${params.toString()}`;
        }

        function toggleMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('active');
        }

        // Crear partículas animadas al mover el mouse
        document.addEventListener('mousemove', (e) => {
            const dot = document.createElement('div');
            dot.classList.add('particle');
            dot.style.left = `${e.clientX}px`;
            dot.style.top = `${e.clientY}px`;
            document.getElementById('background').appendChild(dot);

            setTimeout(() => {
                dot.remove();
            }, 5000);
        });
    </script>
</body>
</html>
