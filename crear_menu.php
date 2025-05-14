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

// Verificar si el usuario tiene rol Administrador
$sql = "SELECT COUNT(*) FROM usuarios_roles WHERE id_usuario = :id_usuario AND id_rol = 1";
$stmt = $db->prepare($sql);
$stmt->bindParam(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT);
$stmt->execute();
$es_admin = $stmt->fetchColumn();

if (!$es_admin) {
    $mensaje = 'Acceso denegado: Solo los administradores pueden crear menús.';
    header('Refresh: 2; url=inicio.php');
    echo "<p style='text-align: center; color: red;'>$mensaje</p>";
    exit();
}

// Obtener menús existentes para el desplegable de padres
$sql = "SELECT id_menu, nombre FROM menus WHERE id_menu_padre IS NULL";
$stmt = $db->prepare($sql);
$stmt->execute();
$menus_padres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Función para generar la URL a partir del nombre
function generarUrl($nombre) {
    // Convertir a minúsculas, reemplazar espacios por guiones, eliminar caracteres especiales
    $url = strtolower(preg_replace('/[^A-Za-z0-9]+/', '_', trim($nombre)));
    return $url . '.php';
}

// Función para crear un archivo PHP básico (opcional)
function crearArchivoBasico($url) {
    $contenido = "<?php\n";
    $contenido .= "session_start();\n";
    $contenido .= "require_once 'Conexion.php';\n";
    $contenido .= "if (!isset(\$_SESSION['id_usuario'])) header('Location: login.php');\n";
    $contenido .= "?>\n";
    $contenido .= "<!DOCTYPE html>\n<html lang=\"es\">\n<head><title>" . basename($url, '.php') . "</title></head>\n";
    $contenido .= "<body>\n<h1>" . basename($url, '.php') . "</h1>\n";
    $contenido .= "<p>Esta página está en desarrollo.</p>\n";
    $contenido .= "<a href=\"inicio.php\">Volver</a>\n</body>\n</html>";

    file_put_contents($url, $contenido);
}

// Procesar creación de menú padre
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_padre'])) {
    $nombre = $_POST['nombre_padre'];
    $url = generarUrl($nombre);

    // Verificar si el nombre ya existe
    $sql = "SELECT COUNT(*) FROM menus WHERE nombre = :nombre";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->execute();
    $existe = $stmt->fetchColumn();

    if ($existe) {
        $mensaje = 'El nombre del menú padre ya existe';
    } else {
        $sql = "INSERT INTO menus (nombre, url, id_menu_padre) VALUES (:nombre, :url, NULL)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':url', $url);
        if ($stmt->execute()) {
            // Crear un archivo PHP básico para la URL generada
            crearArchivoBasico($url);
            $mensaje = 'Menú padre creado correctamente';
            header('Refresh: 0; url=crear_menu.php'); // Recargar para actualizar el desplegable
        } else {
            $mensaje = 'Error al crear el menú padre';
        }
    }
}

// Procesar creación de submenú
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_submenu'])) {
    $nombre = $_POST['nombre_submenu'];
    $url = generarUrl($nombre);
    $id_menu_padre = $_POST['id_menu_padre'] ?? null;

    if ($id_menu_padre === null || $id_menu_padre === '') {
        $mensaje = 'Debes seleccionar un menú padre';
    } else {
        // Validar que id_menu_padre existe
        $sql = "SELECT COUNT(*) FROM menus WHERE id_menu = :id_menu";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id_menu', $id_menu_padre, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $mensaje = 'El menú padre seleccionado no existe';
        } else {
            // Insertar el submenú
            $sql = "INSERT INTO menus (nombre, url, id_menu_padre) VALUES (:nombre, :url, :id_menu_padre)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':url', $url);
            $stmt->bindParam(':id_menu_padre', $id_menu_padre, PDO::PARAM_INT);
            if ($stmt->execute()) {
                // Crear un archivo PHP básico para la URL generada
                crearArchivoBasico($url);
                $mensaje = 'Submenú creado correctamente';
                header('Refresh: 0; url=crear_menu.php');
            } else {
                $mensaje = 'Error al crear el submenú';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Menú</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            display: flex;
            flex-direction: column; /* Para alinear el botón de regreso abajo */
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #ffecd2, #fcb69f); /* Fondo cálido */
            color: #444;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.9); /* Fondo blanco translúcido */
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15); /* Sombra suave */
            width: 500px;
            text-align: center;
            margin-bottom: 30px; /* Espacio para el botón de regreso */
        }

        h2 {
            color: #e65100; /* Naranja llamativo */
            margin-bottom: 30px;
            font-size: 2.8em;
            font-weight: 500;
            letter-spacing: 1px;
        }

        .form-section {
            margin-bottom: 30px;
            padding: 25px;
            border: 1px solid #ffccbc; /* Borde suave */
            border-radius: 8px;
            text-align: left;
            background-color: rgba(255, 255, 255, 0.8); /* Fondo ligeramente transparente */
        }

        .form-section h3 {
            color: #e65100;
            margin-top: 0;
            margin-bottom: 20px;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
            font-size: 1.1em;
        }

        input[type="text"], select {
            width: calc(100% - 22px);
            padding: 12px;
            border: 1px solid #dcdcdc;
            border-radius: 6px;
            font-size: 1em;
            color: #333;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus, select:focus {
            outline: none;
            border-color: #e65100; /* Color de foco */
            box-shadow: 0 2px 6px rgba(230, 81, 0, 0.2);
        }

        button[type="submit"] {
            width: 100%;
            padding: 14px;
            background-color: #e65100; /* Naranja llamativo */
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        button[type="submit"]:hover {
            background-color: #d84315; /* Tono más oscuro al pasar el ratón */
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(230, 81, 0, 0.3);
        }

        .mensaje {
            text-align: center;
            color: #43a047;
            margin-top: 20px;
            font-weight: bold;
        }

        .error {
            text-align: center;
            color: #e53935;
            margin-top: 20px;
            font-weight: bold;
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #795548; /* Marrón terroso */
            color: white;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 1em;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .back-button:hover {
            background-color: #5d4037; /* Tono más oscuro al pasar el ratón */
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        }

        /* Importar fuente Roboto desde Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Crear Menú</h2>
        <?php if ($mensaje): ?>
            <p class="<?php echo strpos($mensaje, 'Error') !== false || strpos($mensaje, 'ya existe') !== false ? 'error' : 'mensaje'; ?>">
                <?php echo $mensaje; ?>
            </p>
        <?php endif; ?>

        <div class="form-section">
            <h3>Crear Menú Padre</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nombre_padre">Nombre del Menú Padre:</label>
                    <input type="text" id="nombre_padre" name="nombre_padre" required>
                </div>
                <button type="submit" name="crear_padre">Crear Menú Padre</button>
            </form>
        </div>

        <div class="form-section">
            <h3>Crear Submenú</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nombre_submenu">Nombre del Submenú:</label>
                    <input type="text" id="nombre_submenu" name="nombre_submenu" required>
                </div>
                <div class="form-group">
                    <label for="id_menu_padre">Menú Padre:</label>
                    <select id="id_menu_padre" name="id_menu_padre" required>
                        <option value="">Selecciona un menú padre</option>
                        <?php foreach ($menus_padres as $menu): ?>
                            <option value="<?php echo $menu['id_menu']; ?>"><?php echo $menu['nombre']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="crear_submenu">Crear Submenú</button>
                <?php if (empty($menus_padres)): ?>
                    <p class="error">No hay menús padres disponibles. Crea un menú padre primero.</p>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <a href="inicio.php" class="back-button">Regresar al Menú</a>
</body>
</html>