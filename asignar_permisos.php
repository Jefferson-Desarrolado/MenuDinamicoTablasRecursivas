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
$mensaje = '';

// Obtener roles
$sql = "SELECT id_rol, nombre FROM roles";
$stmt = $db->prepare($sql);
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener menús y submenús
$sql = "SELECT id_menu, nombre, id_menu_padre FROM menus ORDER BY id_menu_padre IS NULL DESC, id_menu_padre, id_menu";
$stmt = $db->prepare($sql);
$stmt->execute();
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organizar menús y submenús
$menu_organizado = [];
foreach ($menus as $menu) {
    if ($menu['id_menu_padre'] === null) {
        $menu_organizado[$menu['id_menu']] = ['nombre' => $menu['nombre'], 'submenus' => []];
    } else {
        $menu_organizado[$menu['id_menu_padre']]['submenus'][$menu['id_menu']] = $menu['nombre'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_rol = $_POST['id_rol'];
    $permisos = isset($_POST['permisos']) ? $_POST['permisos'] : [];

    // Eliminar permisos existentes para el rol
    $sql = "DELETE FROM permisos WHERE id_rol = :id_rol";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
    $stmt->execute();

    // Insertar nuevos permisos
    foreach ($permisos as $id_menu) {
        $sql = "INSERT INTO permisos (id_rol, id_menu) VALUES (:id_rol, :id_menu)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
        $stmt->bindParam(':id_menu', $id_menu, PDO::PARAM_INT);
        $stmt->execute();
    }

    $mensaje = 'Permisos asignados correctamente';
    header('Refresh: 2; url=inicio.php');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Permisos</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background-color: #f0f0f0; }
        .form-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        select, input[type="checkbox"] { margin-bottom: 10px; }
        button { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .mensaje { text-align: center; color: green; }
        .error { text-align: center; color: red; }
        .menu-group { margin-left: 20px; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Asignar Permisos</h2>
        <?php if ($mensaje): ?>
            <p class="mensaje"><?php echo $mensaje; ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="id_rol">Seleccionar Rol:</label>
                <select id="id_rol" name="id_rol" required>
                    <option value="">Seleccione un rol</option>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?php echo $rol['id_rol']; ?>"><?php echo $rol['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Menús y Submenús:</label>
                <?php foreach ($menu_organizado as $id_menu => $menu): ?>
                    <div>
                        <label>
                            <input type="checkbox" name="permisos[]" value="<?php echo $id_menu; ?>">
                            <?php echo $menu['nombre']; ?>
                        </label>
                        <?php if (!empty($menu['submenus'])): ?>
                            <div class="menu-group">
                                <?php foreach ($menu['submenus'] as $id_submenu => $nombre_submenu): ?>
                                    <label>
                                        <input type="checkbox" name="permisos[]" value="<?php echo $id_submenu; ?>">
                                        <?php echo $nombre_submenu; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="submit">Asignar Permisos</button>
        </form>
    </div>
</body>
</html>