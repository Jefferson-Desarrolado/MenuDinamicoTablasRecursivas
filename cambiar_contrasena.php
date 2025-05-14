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
    $contrasena_actual = $_POST['contrasena_actual'];
    $nueva_contrasena = $_POST['nueva_contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];

    // Validar que las contraseñas nuevas coincidan
    if ($nueva_contrasena !== $confirmar_contrasena) {
        $mensaje = 'Las nuevas contraseñas no coinciden';
    } else {
        // Verificar la contraseña actual
        $sql = "SELECT contrasena FROM usuarios WHERE id_usuario = :id_usuario";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && $usuario['contrasena'] === $contrasena_actual) {
            // Actualizar la contraseña y cambio_contrasena
            $sql = "UPDATE usuarios SET contrasena = :nueva_contrasena, cambio_contrasena = 0 
                    WHERE id_usuario = :id_usuario";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':nueva_contrasena', $nueva_contrasena);
            $stmt->bindParam(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                $mensaje = 'Contraseña actualizada correctamente';
                // Redirigir a inicio.php después de un cambio exitoso
                header('Refresh: 2; url=inicio.php');
            } else {
                $mensaje = 'Error al actualizar la contraseña';
            }
        } else {
            $mensaje = 'La contraseña actual es incorrecta';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background-color: #f0f0f0; }
        .form-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .mensaje { text-align: center; color: green; }
        .error { text-align: center; color: red; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Cambiar Contraseña</h2>
        <?php if ($mensaje): ?>
            <p class="<?php echo strpos($mensaje, 'Error') !== false || strpos($mensaje, 'no coinciden') !== false || strpos($mensaje, 'incorrecta') !== false ? 'error' : 'mensaje'; ?>">
                <?php echo $mensaje; ?>
            </p>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="contrasena_actual">Contraseña Actual:</label>
                <input type="password" id="contrasena_actual" name="contrasena_actual" required>
            </div>
            <div class="form-group">
                <label for="nueva_contrasena">Nueva Contraseña:</label>
                <input type="password" id="nueva_contrasena" name="nueva_contrasena" required>
            </div>
            <div class="form-group">
                <label for="confirmar_contrasena">Confirmar Nueva Contraseña:</label>
                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>
            </div>
            <button type="submit">Cambiar Contraseña</button>
        </form>
    </div>
</body>
</html>