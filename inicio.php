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

// Obtener los permisos del usuario logueado
$sql = "SELECT m.id_menu, m.nombre, m.url, m.id_menu_padre
        FROM menus m
        INNER JOIN permisos p ON m.id_menu = p.id_menu
        INNER JOIN usuarios_roles ur ON p.id_rol = ur.id_rol
        WHERE ur.id_usuario = :id_usuario
        ORDER BY m.id_menu_padre IS NULL DESC, m.id_menu_padre, m.id_menu";
$stmt = $db->prepare($sql);
$stmt->bindParam(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT);
$stmt->execute();
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organizar menús y submenús
$menu_organizado = [];
if ($menus) {
    foreach ($menus as $menu) {
        if ($menu['id_menu_padre'] === null) {
            $menu_organizado[$menu['id_menu']] = [
                'nombre' => $menu['nombre'],
                'url' => $menu['url'] ?? '#', // Valor por defecto si url es nulo
                'submenus' => []
            ];
        } else {
            $menu_organizado[$menu['id_menu_padre']]['submenus'][$menu['id_menu']] = [
                'nombre' => $menu['nombre'],
                'url' => $menu['url'] ?? '#'
            ];
        }
    }
} else {
    $menu_organizado = []; // Evitar error si no hay menús
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Sistema de Gestión</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: #f4f6f8;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .navbar {
            background-color: #343a40; /* Un tono oscuro similar al anterior */
            padding: 10px 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .navbar li {
            position: relative;
        }

        .navbar a {
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            transition: background-color 0.3s ease;
        }

        .navbar a:hover {
            background-color: #495057; /* Un tono más claro al pasar el ratón */
        }

        .navbar .dropdown {
            display: none;
            position: absolute;
            background-color: #343a40;
            min-width: 160px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .navbar li:hover .dropdown {
            display: block;
        }

        .navbar .dropdown a {
            padding: 10px 15px;
            border-bottom: 1px solid #495057;
        }

        .navbar .dropdown a:last-child {
            border-bottom: none;
        }

        .content {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            flex-grow: 1; /* Para que el contenido ocupe el espacio restante */
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        p {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .logout {
            text-align: center;
            margin-top: 30px;
        }

        .logout a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .logout a:hover {
            text-decoration: underline;
            color: #0056b3;
        }

        .error {
            text-align: center;
            color: #dc3545;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <ul>
            <?php if (!empty($menu_organizado)): ?>
                <?php foreach ($menu_organizado as $menu): ?>
                    <li>
                        <a href="<?php echo htmlspecialchars($menu['url']); ?>"><?php echo htmlspecialchars($menu['nombre']); ?></a>
                        <?php if (!empty($menu['submenus'])): ?>
                            <div class="dropdown">
                                <?php foreach ($menu['submenus'] as $submenu): ?>
                                    <a href="<?php echo htmlspecialchars($submenu['url']); ?>"><?php echo htmlspecialchars($submenu['nombre']); ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li><a href="#">No hay menús disponibles</a></li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="content">
        <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></h1>
        <?php if (empty($menu_organizado)): ?>
            <p class="error">No tienes permisos para ver menús. Contacta a un administrador.</p>
        <?php else: ?>
            <p>Este es el panel principal del sistema de gestión. Usa el menú superior para navegar.</p>
        <?php endif; ?>
        <div class="logout">
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </div>
</body>
</html>