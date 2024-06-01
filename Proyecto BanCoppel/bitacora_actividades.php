<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include 'conexion.php';

// Obtener actividades de la base de datos
$stmt = $pdo->prepare("SELECT actividades.*, TO_CHAR(actividades.hora_inicio, 'HH24:MI:SS') as hora_inicio_formateada, TO_CHAR(actividades.hora_fin, 'HH24:MI:SS') as hora_fin_formateada, usuarios.nombre as usuario_nombre FROM actividades LEFT JOIN usuarios ON actividades.numero_empleado = usuarios.numero_empleado ORDER BY CASE WHEN estado = 'completado' THEN 1 ELSE 2 END, fecha DESC");
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
            margin: 0 15px;
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

        .completed td.estado {
            background-color: #c3e6cb;
            color: #155724;
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

        .estado-completado {
            background-color: #c3e6cb;
            font-weight: bold;
        }

        .estado-pendiente {
            background-color: lightcoral;
            font-weight: bold;
        }

        footer {
            text-align: center;
            padding: 10px;
            background-color: #0056b3;
            color: #fff;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <img src="imagenes/coppel-logo2.png" alt="BanCoppel">
            <nav>
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
    </header>
    <div class="container">
        <h1>Bitácora de Actividades</h1>
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
                <tbody>
                    <?php foreach ($actividades as $actividad): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($actividad['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($actividad['descripcion']); ?></td>
                            <td><?php echo htmlspecialchars($actividad['usuario_nombre']); ?></td>
                            <td class="<?php echo $actividad['estado'] == 'completado' ? 'estado-completado' : 'estado-pendiente'; ?>">
                                <?php echo htmlspecialchars($actividad['estado']); ?>
                            </td>
                            <td class="actions">
                                <i class="fa fa-edit" onclick="openEditModal('<?php echo htmlspecialchars(json_encode($actividad)); ?>')"></i>
                                <i class="fa fa-trash" onclick="openDeleteModal(<?php echo $actividad['id']; ?>)"></i>
                                <i class="fa fa-play" onclick="startActivity(<?php echo $actividad['id']; ?>)"></i>
                                <i class="fa fa-stop" onclick="stopActivity(<?php echo $actividad['id']; ?>)"></i>
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

    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }

        function openEditModal(actividad) {
            actividad = JSON.parse(actividad);
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

        document.getElementById('addForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const titulo = document.getElementById('addTitulo').value;
            const descripcion = document.getElementById('addDescripcion').value;
            const estado = document.getElementById('addEstado').value;

            fetch('agregar_actividad.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ titulo, descripcion, estado })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error al agregar la actividad.');
                }
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

        function startActivity(id) {
            fetch('start_activity.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Actividad iniciada.');
                        location.reload(); // Recargar la página para ver los cambios
                    } else {
                        alert('Error al iniciar la actividad.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al iniciar la actividad.');
                });
        }

        function stopActivity(id) {
            fetch('stop_activity.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Actividad detenida.');
                        location.reload(); // Recargar la página para ver los cambios
                    } else {
                        alert('Error al detener la actividad.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al detener la actividad.');
                });
        }
    </script>
</body>
</html>
