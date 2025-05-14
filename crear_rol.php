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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];

    // Verificar si el rol ya existe
    $sql = "SELECT COUNT(*) FROM roles WHERE nombre = :nombre";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->execute();
    $existe = $stmt->fetchColumn();

    if ($existe) {
        $mensaje = 'El rol ya existe';
    } else {
        // Insertar el nuevo rol
        $sql = "INSERT INTO roles (nombre) VALUES (:nombre)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);

        if ($stmt->execute()) {
            $mensaje = 'Rol creado correctamente';
            // Redirigir después de 2 segundos
            header('Refresh: 2; url=inicio.php');
        } else {
            $mensaje = 'Error al crear el rol';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Rol</title>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            display: flex;
            flex-direction: column; /* Para alinear el botón de regreso abajo */
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #6dd5ed, #2193b0); /* Fondo degradado */
            color: #333;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95); /* Fondo blanco translúcido */
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2); /* Sombra más pronunciada */
            width: 400px;
            text-align: center;
            margin-bottom: 20px; /* Espacio entre el formulario y el botón */
        }

        h2 {
            color: #37474f;
            margin-bottom: 30px;
            font-size: 2.5em;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #546e7a;
            font-weight: 500;
            font-size: 1.1em;
        }

        input[type="text"] {
            width: calc(100% - 20px);
            padding: 12px;
            border: 1px solid #b0bec5;
            border-radius: 8px;
            font-size: 1em;
            color: #333;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #00bcd4; /* Color de foco atractivo */
            box-shadow: 0 2px 6px rgba(0, 188, 212, 0.3);
        }

        button[type="submit"] {
            width: 100%;
            padding: 14px;
            background-color: #00bcd4; /* Color llamativo */
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.2em;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        button[type="submit"]:hover {
            background-color: #008ba7; /* Tono más oscuro al pasar el ratón */
            transform: translateY(-2px); /* Ligero efecto de elevación */
            box-shadow: 0 4px 12px rgba(0, 188, 212, 0.4);
        }

        .mensaje {
            text-align: center;
            color: #4caf50;
            margin-top: 20px;
            font-weight: bold;
        }

        .error {
            text-align: center;
            color: #f44336;
            margin-top: 20px;
            font-weight: bold;
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #6c757d; /* Color grisáceo */
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 1em;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .back-button:hover {
            background-color: #545b62; /* Tono más oscuro al pasar el ratón */
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        /* Importar fuente Montserrat desde Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap');
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Crear Rol</h2>
        <?php if ($mensaje): ?>
            <p class="<?php echo strpos($mensaje, 'Error') !== false || strpos($mensaje, 'ya existe') !== false ? 'error' : 'mensaje'; ?>">
                <?php echo $mensaje; ?>
            </p>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="nombre">Nombre del Rol:</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <button type="submit">Crear Rol</button>
        </form>
    </div>
    <a href="inicio.php" class="back-button">Regresar al Menú</a>
</body>
</html>