<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'conexion.php';

// Establecer la zona horaria de Mazatlán, Culiacán, Sinaloa
date_default_timezone_set('America/Mazatlan');

// Obtener la hora actual
$horaActual = date('H:i:s'); // Formato de 24 horas

// Determinar la fecha basada en la hora actual
if ($horaActual >= '00:00:00' && $horaActual < '12:00:00') {
    $fecha = date('Y-m-d');
} else {
    $fecha = date('Y-m-d', strtotime('+1 day'));
}

// Verificar la hora y fecha
echo "Hora actual: " . $horaActual . "<br>";
echo "Fecha utilizada: " . $fecha . "<br>";

// Registrar la hora y fecha en un archivo de log
//file_put_contents('fecha_log.txt', "Hora actual: $horaActual - Fecha utilizada: $fecha\n", FILE_APPEND);

// Resto del código para manejar la bitácora de actividades
$stmt = $pdo->prepare("SELECT actividades.*, TO_CHAR(actividades.hora_inicio, 'HH24:MI:SS') as hora_inicio_formateada, TO_CHAR(actividades.hora_fin, 'HH24:MI:SS') as hora_fin_formateada, usuarios.nombre as usuario_nombre FROM actividades LEFT JOIN usuarios ON actividades.numero_empleado = usuarios.numero_empleado WHERE fecha = :fecha ORDER BY CASE WHEN estado = 'completado' THEN 1 WHEN estado = 'activo' THEN 2 ELSE 3 END");
$stmt->bindParam(':fecha', $fecha);
$stmt->execute();
$actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si hay actividades
if (!$actividades) {
    $actividades = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitácora de Actividades</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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

        .container {
            flex: 1;
            padding: 20px;
        }

        .table-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-height: 600px;
            overflow-y: auto;
            position: relative;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead th {
            position:auto;
            top: 0;
            background-color: #f4f4f4;
            z-index: 2;
            border: 1px solid #444;
        }

        table th, table td {
            padding: 10px;
            border: 1px solid #444;
            text-align: left;
            word-wrap: break-word;
        }

        .estado-completado {
            background-color: #c3e6cb;
            font-weight: bold;
        }

        .estado-activo {
            background-color: yellow;
            font-weight: bold;
        }

        .estado-pendiente {
            background-color: lightcoral;
            font-weight: bold;
        }

        .actions i {
            cursor: pointer;
            margin-left: 10px;
            font-size: 20px;
            color: #007bff;
            transition: color 0.3s ease-in-out;
        }

        .actions i:hover {
            color: #0056b3;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-sizing: border-box;
        }

        .modal-content input, .modal-content textarea, .modal-content select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .modal-content button {
            padding: 10px 20px;
            border: none;
            background-color: #007bff;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
            margin: 5px;
        }

        .modal-content button:hover {
            background-color: #0056b3;
        }

        footer {
            text-align: center;
            padding: 10px;
            background-color: #0056b3;
            color: #fff;
        }

        .filter-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .filter-container input {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .filter-container button {
            padding: 10px 20px;
            border: none;
            background-color: #007bff;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
        }

        .filter-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <img src="imagenes/coppel-logo2.png" alt="BanCoppel">
            <nav>
                <ul>
                    <li><a href="logout.php">Cerrar Sesion</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <div class="container">
        <h1>Bitácora de Actividades</h1>
        <div class="filter-container">
            <div>
                <input type="date" id="filterFecha" value="<?php echo date('Y-m-d'); ?>">
                <button onclick="filterActivities()">Buscar</button>
            </div>
            <div>
                <button onclick="openCopyModal()">Copiar Actividades</button>
                <button onclick="deleteActivitiesByDate()">Eliminar Actividades del Día</button>
            </div>
        </div>
        <div class="actions-container">
            <i class="fa fa-plus" onclick="openAddModal()"></i>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="activitiesTable">
                    <?php foreach ($actividades as $actividad): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($actividad['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($actividad['descripcion']); ?></td>
                            <td><?php echo htmlspecialchars($actividad['usuario_nombre']); ?></td>
                            <td class="<?php echo 'estado-' . strtolower($actividad['estado']); ?>">
                                <?php echo htmlspecialchars($actividad['estado']); ?>
                            </td>
                            <td class="actions">
                                <i class="fa fa-edit" onclick='openEditModal(<?php echo json_encode($actividad); ?>)'></i>
                                <i class="fa fa-trash" onclick="openDeleteModal(<?php echo $actividad['id']; ?>)"></i>
                                <i class="fa fa-play" onclick="startActivity(<?php echo $actividad['id']; ?>, '<?php echo date('Y-m-d'); ?>')"></i>
                                <i class="fa fa-stop" onclick="stopActivity(<?php echo $actividad['id']; ?>, '<?php echo date('Y-m-d'); ?>')"></i>
                                <i class="fa fa-exchange-alt" onclick='openTransferModal(<?php echo json_encode($actividad); ?>)'></i>
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

    <!-- Modal para agregar actividad -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <h2>Agregar Actividad</h2>
            <form id="addForm">
                <input type="text" id="addTitulo" placeholder="Título" required>
                <textarea id="addDescripcion" placeholder="Descripción" required></textarea>
                <select id="addEstado" required>
                    <option value="pendiente">Pendiente</option>
                    <option value="activo">Activo</option>
                    <option value="completado">Completado</option>
                </select>
                <button type="submit">Agregar</button>
                <button type="button" onclick="closeAddModal()">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Modal para editar actividad -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <h2>Editar Actividad</h2>
            <form id="editForm">
                <input type="hidden" id="editId">
                <input type="text" id="editTitulo" placeholder="Título" required>
                <textarea id="editDescripcion" placeholder="Descripción" required></textarea>
                <select id="editEstado" required>
                    <option value="pendiente">Pendiente</option>
                    <option value="activo">Activo</option>
                    <option value="completado">Completado</option>
                </select>
                <button type="submit">Guardar Cambios</button>
                <button type="button" onclick="closeEditModal()">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Modal para eliminar actividad -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <h2>Confirmar Eliminación</h2>
            <p>¿Estás seguro de que deseas eliminar esta actividad?</p>
            <button id="confirmDeleteBtn">Eliminar</button>
            <button type="button" onclick="closeDeleteModal()">Cancelar</button>
        </div>
    </div>

<!-- Modal para transferir actividad -->
<div class="modal" id="transferModal">
    <div class="modal-content">
        <h2>Transferir Actividad</h2>
        <form id="transferForm">
            <input type="hidden" id="transferActividadId">
            <label for="nuevoUsuario">Selecciona el nuevo usuario:</label>
            <select id="nuevoUsuario" required>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?php echo $usuario['numero_empleado']; ?>">
                        <?php echo htmlspecialchars($usuario['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Transferir</button>
            <button type="button" onclick="closeTransferModal()">Cancelar</button>
        </form>
    </div>
</div>


    <!-- Modal para copiar actividad -->
    <div class="modal" id="copyModal">
        <div class="modal-content">
            <h2>Copiar Actividades</h2>
            <form id="copyForm">
                <label for="copyFromDate">Selecciona la fecha de origen:</label>
                <input type="date" id="copyFromDate" required>
                <label for="copyToDate">Selecciona la fecha de destino:</label>
                <input type="date" id="copyToDate" required>
                <button type="submit">Realizar Copiado</button>
                <button type="button" onclick="closeCopyModal()">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('filterFecha').value = today;
            filterActivities();
        });

        function filterActivities() {
            const fecha = document.getElementById('filterFecha').value;
            if (fecha) {
                fetch(`filtrar_actividades.php?fecha=${fecha}`)
                    .then(response => response.json())
                    .then(data => {
                        const tbody = document.getElementById('activitiesTable');
                        tbody.innerHTML = '';
                        data.actividades.forEach(actividad => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${actividad.titulo}</td>
                                <td>${actividad.descripcion}</td>
                                <td>${actividad.usuario_nombre}</td>
                                <td class="estado-${actividad.estado.toLowerCase()}">${actividad.estado}</td>
                                <td class="actions">
                                    <i class="fa fa-edit" onclick='openEditModal(${JSON.stringify(actividad)})'></i>
                                    <i class="fa fa-trash" onclick="openDeleteModal(${actividad.id})"></i>
                                    <i class="fa fa-play" onclick="startActivity(${actividad.id}, '${fecha}')"></i>
                                    <i class="fa fa-stop" onclick="stopActivity(${actividad.id}, '${fecha}')"></i>
                                    <i class="fa fa-exchange-alt" onclick='openTransferModal(${JSON.stringify(actividad)})'></i>
                                </td>
                            `;
                            tbody.appendChild(tr);
                        });
                    });
            }
        }

        function startActivity(id, fecha) {
            fetch('start_activity.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Actividad iniciada.');
                        filterActivitiesByDate(fecha);
                    } else {
                        alert('Error al iniciar la actividad.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al iniciar la actividad.');
                });
        }

        function stopActivity(id, fecha) {
            fetch('stop_activity.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Actividad detenida.');
                        filterActivitiesByDate(fecha);
                    } else {
                        alert('Error al detener la actividad.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al detener la actividad.');
                });
        }

        function filterActivitiesByDate(fecha) {
            fetch(`filtrar_actividades.php?fecha=${fecha}`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('activitiesTable');
                    tbody.innerHTML = '';
                    data.actividades.forEach(actividad => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${actividad.titulo}</td>
                            <td>${actividad.descripcion}</td>
                            <td>${actividad.usuario_nombre}</td>
                            <td class="estado-${actividad.estado.toLowerCase()}">${actividad.estado}</td>
                            <td class="actions">
                                <i class="fa fa-edit" onclick='openEditModal(${JSON.stringify(actividad)})'></i>
                                <i class="fa fa-trash" onclick="openDeleteModal(${actividad.id})"></i>
                                <i class="fa fa-play" onclick="startActivity(${actividad.id}, '${fecha}')"></i>
                                <i class="fa fa-stop" onclick="stopActivity(${actividad.id}, '${fecha}')"></i>
                                <i class="fa fa-exchange-alt" onclick='openTransferModal(${JSON.stringify(actividad)})'></i>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                });
        }

        function deleteActivitiesByDate() {
    const fecha = document.getElementById('filterFecha').value;
    if (fecha) {
        if (confirm('¿Estás seguro de que deseas eliminar todas las actividades del ' + fecha + '?')) {
            fetch('eliminar_actividades_por_fecha.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ fecha: fecha })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Actividades eliminadas exitosamente.');
                    filterActivities(); // Refrescar la tabla
                } else {
                    alert('Error al eliminar las actividades: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error al eliminar las actividades.');
                console.error('Error:', error);
            });
        }
    } else {
        alert('Por favor selecciona una fecha.');
    }
}
document.getElementById('transferForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const actividad_id = document.getElementById('transferActividadId').value;
    const nuevo_user_id = document.getElementById('nuevoUsuario').value;

    console.log('Datos enviados:', { actividad_id, nuevo_user_id });

    fetch('transferir_actividad.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ actividad_id, nuevo_user_id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Transferencia exitosa:', data);
            location.reload();
        } else {
            console.log('Error en la transferencia:', data.message);
            alert('Error al transferir la actividad: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al realizar la solicitud.');
    });
});

        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('editId').value;
            const titulo = document.getElementById('editTitulo').value;
            const descripcion = document.getElementById('editDescripcion').value;
            const estado = document.getElementById('editEstado').value;

            fetch('editar_actividad.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id, titulo, descripcion, estado })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error al actualizar la actividad.');
                }
            });
        });

        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            fetch('eliminar_actividad.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error al eliminar la actividad.');
                    }
                });
        });

        document.getElementById('copyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const fromDate = document.getElementById('copyFromDate').value;
            const toDate = document.getElementById('copyToDate').value;

            fetch('copiar_actividades.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ fromDate, toDate })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Actividades copiadas exitosamente.');
                    location.reload();
                } else {
                    alert('Error al copiar las actividades: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error al copiar las actividades.');
                console.error('Error:', error);
            });
        });

        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }

        function openEditModal(actividad) {
            document.getElementById('editId').value = actividad.id;
            document.getElementById('editTitulo').value = actividad.titulo;
            document.getElementById('editDescripcion').value = actividad.descripcion;
            document.getElementById('editEstado').value = actividad.estado;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function openDeleteModal(id) {
            document.getElementById('confirmDeleteBtn').setAttribute('data-id', id);
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        function openTransferModal(actividad) {
    fetch('obtener_usuarios.php') // Archivo PHP que devuelve la lista de usuarios
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('nuevoUsuario');
            select.innerHTML = ''; // Limpia las opciones previas
            data.usuarios.forEach(usuario => {
                const option = document.createElement('option');
                option.value = usuario.numero_empleado;
                option.textContent = usuario.nombre;
                select.appendChild(option);
            });
            document.getElementById('transferActividadId').value = actividad.id;
            document.getElementById('transferModal').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error al obtener los usuarios:', error);
            alert('No se pudo cargar la lista de usuarios.');
        });
}

        function closeTransferModal() {
            document.getElementById('transferModal').style.display = 'none';
        }

        function openCopyModal() {
            document.getElementById('copyModal').style.display = 'flex';
        }

        function closeCopyModal() {
            document.getElementById('copyModal').style.display = 'none';
        }
    </script>
</body>
</html>
