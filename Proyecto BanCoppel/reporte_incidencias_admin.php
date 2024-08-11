<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'conexion.php';

// Obtener la fecha seleccionada o usar la fecha actual por defecto
$fecha = date('Y-m-d');
if (isset($_GET['fecha'])) {
    $fecha = $_GET['fecha'];
}

// Consulta a la base de datos para obtener las incidencias filtradas por fecha
$queryIncidencias = "SELECT estado, COUNT(*) as count 
                     FROM incidencias 
                     WHERE DATE(fecha) = :fecha 
                     GROUP BY estado";
$stmtIncidencias = $pdo->prepare($queryIncidencias);
$stmtIncidencias->bindParam(':fecha', $fecha);
$stmtIncidencias->execute();
$incidencias = $stmtIncidencias->fetchAll(PDO::FETCH_ASSOC);

// Preparar datos para el gráfico
$incidencias_por_estado = [
    'pendiente' => 0,
    'en_proceso' => 0,
    'resuelta' => 0
];

foreach ($incidencias as $incidencia) {
    $incidencias_por_estado[$incidencia['estado']] = $incidencia['count'];
}

// Convertir datos a formato JSON para JavaScript
$incidencias_por_estado_json = json_encode(array_values($incidencias_por_estado));

// Consulta a la base de datos para obtener las incidencias por usuario y estado
$queryIncidenciasUsuario = "SELECT u.nombre as usuario_nombre, i.estado, COUNT(*) as count 
                            FROM incidencias i 
                            JOIN usuarios u ON i.numero_empleado = u.numero_empleado 
                            WHERE DATE(i.fecha) = :fecha 
                            GROUP BY u.nombre, i.estado";
$stmtIncidenciasUsuario = $pdo->prepare($queryIncidenciasUsuario);
$stmtIncidenciasUsuario->bindParam(':fecha', $fecha);
$stmtIncidenciasUsuario->execute();
$incidencias_usuario = $stmtIncidenciasUsuario->fetchAll(PDO::FETCH_ASSOC);

// Preparar datos para la tabla
$incidencias_por_usuario_y_estado = [];

foreach ($incidencias_usuario as $fila) {
    $usuario_nombre = $fila['usuario_nombre'];
    $estado = $fila['estado'];

    if (!isset($incidencias_por_usuario_y_estado[$usuario_nombre])) {
        $incidencias_por_usuario_y_estado[$usuario_nombre] = [
            'pendiente' => 0,
            'en_proceso' => 0,
            'resuelta' => 0
        ];
    }

    $incidencias_por_usuario_y_estado[$usuario_nombre][$estado] = $fila['count'];
}

// Exportar incidencias a CSV
if (isset($_GET['tipo']) && $_GET['tipo'] === 'incidencias') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=incidencias_reporte.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Descripcion', 'Nombre de quien reporta', 'Fecha', 'Estado']);

    $query = $pdo->prepare("
        SELECT i.descripcion, u.nombre AS nombre_reporto, i.fecha, i.estado
        FROM incidencias i
        JOIN usuarios u ON i.numero_empleado = u.numero_empleado
        WHERE DATE(i.fecha) = :fecha
    ");
    $query->bindParam(':fecha', $fecha);
    $query->execute();

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
    <title>Reporte de Incidencias</title>
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
        <form method="get" action="reporte_incidencias_admin.php">
            <label for="fecha">Seleccionar Fecha:</label>
            <input type="date" id="fecha" name="fecha" value="<?php echo htmlspecialchars($fecha); ?>">
            <button type="submit">Filtrar</button>
        </form>
        <h2>Estado de Incidencias</h2>
        <div class="chart-container">
            <canvas id="incidenciasChart"></canvas>
        </div>
        <h2>Detalles de Incidencias</h2>
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Pendientes</th>
                    <th>En Proceso</th>
                    <th>Resueltas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incidencias_por_usuario_y_estado as $usuario => $estados): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario); ?></td>
                        <td><?php echo htmlspecialchars($estados['pendiente']); ?></td>
                        <td><?php echo htmlspecialchars($estados['en_proceso']); ?></td>
                        <td><?php echo htmlspecialchars($estados['resuelta']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button class="download-button" onclick="descargarCSV('incidencias')">Descargar Incidencias CSV</button>
    </div>
    <footer>
        <p>&copy; 2024 BanCoppel Derechos Reservados</p>
    </footer>
    <script>
        const ctxIncidencias = document.getElementById('incidenciasChart').getContext('2d');
        const incidenciasChart = new Chart(ctxIncidencias, {
            type: 'bar',
            data: {
                labels: ['Pendientes', 'En Proceso', 'Resueltas'],
                datasets: [{
                    label: 'Incidencias',
                    data: <?php echo $incidencias_por_estado_json; ?>,
                    backgroundColor: ['red', 'yellow', 'green']
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
                        text: 'Estado de Incidencias'
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
            const fecha = document.getElementById('fecha').value;
            window.location.href = `reporte_incidencias_admin.php?tipo=${tipo}&fecha=${fecha}`;
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
