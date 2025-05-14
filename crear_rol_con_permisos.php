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
    $mensaje = 'Acceso denegado: Solo los administradores pueden asignar permisos a roles.';
    header('Refresh: 2; url=inicio.php');
    echo "<p style='text-align: center; color: red;'>$mensaje</p>";
    exit();
}

$mensaje = '';

// Obtener todos los roles existentes
$sql = "SELECT id_rol, nombre FROM roles";
$stmt = $db->prepare($sql);
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los menús y submenús
$sql_menus = "SELECT id_menu, nombre, id_menu_padre FROM menus ORDER BY id_menu_padre IS NULL DESC, id_menu_padre, id_menu";
$stmt_menus = $db->prepare($sql_menus);
$stmt_menus->execute();
$menus = $stmt_menus->fetchAll(PDO::FETCH_ASSOC);

// Función para organizar menús y submenús
function organizarMenus($menus) {
    $menu_organizado = [];
    foreach ($menus as $menu) {
        if ($menu['id_menu_padre'] === null) {
            $menu_organizado[$menu['id_menu']] = ['nombre' => $menu['nombre'], 'submenus' => []];
        } else {
            $menu_organizado[$menu['id_menu_padre']]['submenus'][$menu['id_menu']] = $menu['nombre'];
        }
    }
    return $menu_organizado;
}

$menu_organizado = organizarMenus($menus);

// Obtener permisos ya asignados al rol seleccionado (si existe)
$permisos_existentes = [];
$id_rol_seleccionado = isset($_POST['id_rol']) ? $_POST['id_rol'] : null;
if (isset($_GET['id_rol'])) {
    $id_rol_seleccionado = $_GET['id_rol'];
}

if ($id_rol_seleccionado) {
    $sql_permisos = "SELECT id_menu FROM permisos WHERE id_rol = :id_rol";
    $stmt_permisos = $db->prepare($sql_permisos);
    $stmt_permisos->bindParam(':id_rol', $id_rol_seleccionado, PDO::PARAM_INT);
    $stmt_permisos->execute();
    $permisos_existentes = array_column($stmt_permisos->fetchAll(PDO::FETCH_ASSOC), 'id_menu');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmar'])) {
    $id_rol = $_POST['id_rol'];
    $permisos = isset($_POST['permisos']) ? $_POST['permisos'] : [];

    // Insertar nuevos permisos (sin eliminar los existentes)
    foreach ($permisos as $id_menu) {
        // Verificar si el permiso ya existe para este rol
        $sql_verificar = "SELECT COUNT(*) FROM permisos WHERE id_rol = :id_rol AND id_menu = :id_menu";
        $stmt_verificar = $db->prepare($sql_verificar);
        $stmt_verificar->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
        $stmt_verificar->bindParam(':id_menu', $id_menu, PDO::PARAM_INT);
        $stmt_verificar->execute();
        $existe = $stmt_verificar->fetchColumn();

        if (!$existe) {
            $sql_insertar = "INSERT INTO permisos (id_rol, id_menu) VALUES (:id_rol, :id_menu)";
            $stmt_insertar = $db->prepare($sql_insertar);
            $stmt_insertar->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
            $stmt_insertar->bindParam(':id_menu', $id_menu, PDO::PARAM_INT);
            $stmt_insertar->execute();
        }
    }

    $mensaje = 'Permisos asignados correctamente al rol';
    header('Refresh: 2; url=inicio.php');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Permisos a Rol</title>
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
            width: 80%;
            max-width: 600px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
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
            margin-bottom: 15px;
            font-size: 16px;
            color: #333;
        }

        input[type="checkbox"] {
            margin-right: 8px;
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

        .menu-group {
            margin-left: 20px;
            padding-left: 15px;
            border-left: 1px solid #eee;
        }

        .back-button {
            display: block;
            width: fit-content;
            padding: 10px 15px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #545b62;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Asignar Permisos a Rol</h2>
        <?php if ($mensaje): ?>
            <p class="<?php echo strpos($mensaje, 'Error') !== false ? 'error' : 'mensaje'; ?>">
                <?php echo $mensaje; ?>
            </p>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="id_rol">Seleccionar Rol:</label>
                <select id="id_rol" name="id_rol" required onchange="cargarPermisos(this.value)">
                    <option value="">Selecciona un rol</option>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?php echo $rol['id_rol']; ?>" <?php echo $id_rol_seleccionado == $rol['id_rol'] ? 'selected' : ''; ?>>
                            <?php echo $rol['nombre']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($roles)): ?>
                    <p class="error">No hay roles disponibles. Crea un rol primero.</p>
                <?php endif; ?>
            </div>

            <div id="permisos-container" class="form-group">
                <?php if ($id_rol_seleccionado): ?>
                    <label>Menús y Submenús disponibles para asignar:</label>
                    <?php
                    $hay_menus_disponibles = false;
                    foreach ($menu_organizado as $id_menu => $menu):
                        // Mostrar el menú padre solo si no está asignado
                        if (!in_array($id_menu, $permisos_existentes)):
                            $hay_menus_disponibles = true;
                    ?>
                            <div>
                                <label>
                                    <input type="checkbox" name="permisos[]" value="<?php echo $id_menu; ?>">
                                    <?php echo $menu['nombre']; ?>
                                </label>
                                <?php if (!empty($menu['submenus'])): ?>
                                    <div class="menu-group">
                                        <?php foreach ($menu['submenus'] as $id_submenu => $nombre_submenu): ?>
                                            <?php if (!in_array($id_submenu, $permisos_existentes)): ?>
                                                <label>
                                                    <input type="checkbox" name="permisos[]" value="<?php echo $id_submenu; ?>">
                                                    <?php echo $nombre_submenu; ?>
                                                </label>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <?php
                            // Si el menú padre está asignado, verificar si hay submenús no asignados
                            $submenus_no_asignados = array_filter($menu['submenus'], function($id_submenu) use ($permisos_existentes) {
                                return !in_array($id_submenu, $permisos_existentes);
                            }, ARRAY_FILTER_USE_KEY);
                            if (!empty($submenus_no_asignados)):
                                $hay_menus_disponibles = true;
                            ?>
                                <div>
                                    <label><?php echo $menu['nombre']; ?> (ya asignado)</label>
                                    <div class="menu-group">
                                        <?php foreach ($submenus_no_asignados as $id_submenu => $nombre_submenu): ?>
                                            <label>
                                                <input type="checkbox" name="permisos[]" value="<?php echo $id_submenu; ?>">
                                                <?php echo $nombre_submenu; ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php if (!$hay_menus_disponibles): ?>
                        <p class="error">No hay menús o submenús disponibles para asignar a este rol.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php if ($id_rol_seleccionado): ?>
                <input type="hidden" name="id_rol" value="<?php echo $id_rol_seleccionado; ?>">
                <button type="submit" name="confirmar" <?php echo !$hay_menus_disponibles ? 'disabled' : ''; ?>>Confirmar y Asignar Permisos</button>
            <?php endif; ?>
        </form>

        <a href="inicio.php" class="back-button">Regresar al Menú</a>
    </div>

    <script>
        function cargarPermisos(idRol) {
            if (idRol) {
                window.location.href = '?id_rol=' + idRol;
            } else {
                document.getElementById('permisos-container').innerHTML = '';
            }
        }
    </script>
</body>
</html>