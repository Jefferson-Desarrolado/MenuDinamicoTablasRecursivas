<?php
session_start();
require_once 'Conexion.php';
if (!isset($_SESSION['id_usuario'])) header('Location: login.php');
?>
<!DOCTYPE html>
<html lang="es">
<head><title>consultas</title></head>
<body>
<h1>consultas</h1>
<p>Esta página está en desarrollo.</p>
<a href="inicio.php">Volver</a>
</body>
</html>