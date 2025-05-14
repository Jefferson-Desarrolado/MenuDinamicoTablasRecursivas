<?php
session_start();
require_once 'MetodosDeber.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$metodos = new MetodosDeber();
$mensaje = "";

// Procesar cambio de contraseña
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nuevaClave = $_POST['nueva_clave'] ?? '';
    $confirmarClave = $_POST['confirmar_clave'] ?? '';

    if ($nuevaClave === $confirmarClave && strlen($nuevaClave) >= 6) {
        $usuarioId = $_SESSION['usuario_id'];
        if ($metodos->cambiarClave($usuarioId, $nuevaClave)) {
            header("Location: dashboard.php");
            exit();
        } else {
            $mensaje = "Error al actualizar la contraseña.";
        }
    } else {
        $mensaje = "Las contraseñas no coinciden o son muy cortas (mínimo 6 caracteres).";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cambiar Contraseña</title>
</head>
<body>
    <h2>Cambiar Contraseña</h2>
    <?php if ($mensaje): ?>
        <p style="color:red;"><?php echo $mensaje; ?></p>
    <?php endif; ?>
    <form method="POST">
        <label>Nueva Contraseña:</label>
        <input type="password" name="nueva_clave" required><br><br>

        <label>Confirmar Contraseña:</label>
        <input type="password" name="confirmar_clave" required><br><br>

        <button type="submit">Guardar</button>
    </form>
</body>
</html>
