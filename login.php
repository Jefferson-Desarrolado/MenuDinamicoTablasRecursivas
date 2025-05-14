<?php
session_start();
require_once 'MetodosDeber.php';

$metodos = new MetodosDeber();
$mensaje = "";

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_POST['usuario'] ?? '';
    $clave = $_POST['clave'] ?? '';

    $usuarioData = $metodos->verificarLogin($usuario, $clave);

    if ($usuarioData) {
        $_SESSION['usuario_id'] = $usuarioData['id'];
        $_SESSION['nombre'] = $usuarioData['nombre'];
        $_SESSION['rol_id'] = $usuarioData['rol_id'];

        if ($usuarioData['cambio_clave']) {
            header("Location: cambiar_clave.php");
        } else {
            header("Location: dashboard.php"); // Página principal después de login
        }
        exit();
    } else {
        $mensaje = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Iniciar Sesión</h2>
    <?php if ($mensaje): ?>
        <p style="color:red;"><?php echo $mensaje; ?></p>
    <?php endif; ?>
    <form method="POST">
        <label>Usuario:</label>
        <input type="text" name="usuario" required><br><br>
        
        <label>Contraseña:</label>
        <input type="password" name="clave" required><br><br>
        
        <button type="submit">Ingresar</button>
    </form>
</body>
</html>
