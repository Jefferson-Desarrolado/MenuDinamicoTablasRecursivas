<?php
session_start();
require_once 'Conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}

$conexion = new Conexion();
$db = $conexion->getConexion();

// Verificar si el usuario tiene rol Administrador
$sql = "SELECT COUNT(*) FROM usuarios_roles WHERE id_usuario = :id_usuario AND id_rol = 1";
$stmt = $db->prepare($sql);
$stmt->bindParam(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT);
$stmt->execute();
$es_admin = $stmt->fetchColumn();

if (!$es_admin) {
    $mensaje = 'Acceso denegado: Solo los administradores pueden asignar roles a usuarios.';
    header('Refresh: 2; url=inicio.php');
    echo "<p style='text-align: center; color: red;'>$mensaje</p>";
    exit();
}

$mensaje = '';

// Obtener todos los usuarios
$sql = "SELECT id_usuario, usuario FROM usuarios";
$stmt = $db->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los roles existentes
$sql = "SELECT id_rol, nombre FROM roles";
$stmt = $db->prepare($sql);
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener roles asignados al usuario seleccionado (inicialmente nulo)
$roles_usuario = [];
$id_usuario_seleccionado = isset($_POST['id_usuario']) ? $_POST['id_usuario'] : null;
if (isset($_GET['id_usuario'])) {
    $id_usuario_seleccionado = $_GET['id_usuario'];
}

if ($id_usuario_seleccionado) {
    $sql = "SELECT r.id_rol, r.nombre
            FROM roles r
            JOIN usuarios_roles ur ON r.id_rol = ur.id_rol
            WHERE ur.id_usuario = :id_usuario";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario_seleccionado, PDO::PARAM_INT);
    $stmt->execute();
    $roles_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmar'])) {
    $id_usuario = $_POST['id_usuario'];
    $id_rol = $_POST['id_rol'];

    // Verificar si el usuario ya tiene el rol
    $sql = "SELECT COUNT(*) FROM usuarios_roles WHERE id_usuario = :id_usuario AND id_rol = :id_rol";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
    $stmt->execute();
    $rol_ya_asignado = $stmt->fetchColumn();

    if ($rol_ya_asignado) {
        $mensaje = 'El usuario ya tiene este rol asignado.';
    } else {
        // Asignar el rol al usuario
        $sql = "INSERT INTO usuarios_roles (id_usuario, id_rol) VALUES (:id_usuario, :id_rol)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $mensaje = 'Rol asignado correctamente al usuario.';
            // Recargar la página para mostrar el nuevo rol asignado
            header('Location: asignar_rol.php?id_usuario=' . $id_usuario);
            exit();
        } else {
            $mensaje = 'Error al asignar el rol al usuario.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Rol a Usuario</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f4f6f8;
        }

        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }

        h2 {
            color: #333;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }

        select {
            width: calc(100% - 16px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            color: #333;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .mensaje {
            text-align: center;
            color: #28a745;
            margin-top: 15px;
        }

        .error {
            text-align: center;
            color: #dc3545;
            margin-top: 15px;
        }

        .warning {
            text-align: center;
            color: #ffc107;
            margin-bottom: 15px;
        }

        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #545b62;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Asignar Rol a Usuario</h2>
        <?php if ($mensaje): ?>
            <p class="<?php echo strpos($mensaje, 'Error') !== false || strpos($mensaje, 'ya tiene') !== false ? 'error' : 'mensaje'; ?>">
                <?php echo $mensaje; ?>
            </p>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="id_usuario">Seleccionar Usuario:</label>
                <select id="id_usuario" name="id_usuario" required onchange="cargarRolesUsuario(this.value)">
                    <option value="">Selecciona un usuario</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?php echo $usuario['id_usuario']; ?>" <?php echo $id_usuario_seleccionado == $usuario['id_usuario'] ? 'selected' : ''; ?>>
                            <?php echo $usuario['usuario']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($usuarios)): ?>
                    <p class="error">No hay usuarios disponibles. Crea un usuario primero.</p>
                <?php endif; ?>
            </div>

            <div id="roles-usuario-container">
                <?php if ($id_usuario_seleccionado): ?>
                    <?php if (!empty($roles_usuario)): ?>
                        <p class="warning">
                            Este usuario ya tiene los siguientes roles asignados:
                            <?php echo implode(', ', array_column($roles_usuario, 'nombre')); ?>.
                        </p>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="id_rol">Seleccionar Rol para Asignar:</label>
                        <select id="id_rol" name="id_rol" required>
                            <option value="">Selecciona un rol</option>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?php echo $rol['id_rol']; ?>">
                                    <?php echo $rol['nombre']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($roles)): ?>
                            <p class="error">No hay roles disponibles. Crea un rol primero.</p>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="id_usuario" value="<?php echo $id_usuario_seleccionado; ?>">
                    <button type="submit" name="confirmar">Asignar Rol</button>
                <?php endif; ?>
            </div>
        </form>
        <a href="inicio.php" class="back-button">Regresar a Inicio</a>
    </div>

    <script>
        function cargarRolesUsuario(idUsuario) {
            if (idUsuario) {
                window.location.href = '?id_usuario=' + idUsuario;
            } else {
                document.getElementById('roles-usuario-container').innerHTML = '';
            }
        }
    </script>
</body>
</html>