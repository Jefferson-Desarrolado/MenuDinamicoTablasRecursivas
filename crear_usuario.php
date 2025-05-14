<?php
session_start();
require_once 'Conexion.php';

$conexion = new Conexion();
$db = $conexion->getConexion();
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];

    // Validar que las contraseñas coincidan
    if ($contrasena !== $confirmar_contrasena) {
        $mensaje = 'Las contraseñas no coinciden';
    } else {
        // Verificar si el usuario o email ya existen
        $sql = "SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario OR email = :email";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $existe = $stmt->fetchColumn();

        if ($existe) {
            $mensaje = 'El usuario o email ya está registrado';
        } else {
            // Insertar el nuevo usuario
            $sql = "INSERT INTO usuarios (nombre, apellido, email, usuario, contrasena, cambio_contrasena)
                    VALUES (:nombre, :apellido, :email, :usuario, :contrasena, 1)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->bindParam(':contrasena', $contrasena);

            if ($stmt->execute()) {
                $mensaje = 'Usuario registrado correctamente. <a href="login.php">Iniciar Sesión</a>';
            } else {
                $mensaje = 'Error al registrar el usuario';
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
    <title>Registrar Usuario</title>
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
        .login-link { text-align: center; margin-top: 10px; }
        .login-link a { color: #007bff; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Registrar Usuario</h2>
        <?php if ($mensaje): ?>
            <p class="<?php echo strpos($mensaje, 'Error') !== false || strpos($mensaje, 'no coinciden') !== false || strpos($mensaje, 'ya está registrado') !== false ? 'error' : 'mensaje'; ?>">
                <?php echo $mensaje; ?>
            </p>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>
            <div class="form-group">
                <label for="contrasena">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" required>
            </div>
            <div class="form-group">
                <label for="confirmar_contrasena">Confirmar Contraseña:</label>
                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>
            </div>
            <button type="submit">Registrar</button>
        </form>
        <div class="login-link">
            <a href="login.php">Volver al Login</a>
        </div>
    </div>
</body>
</html>