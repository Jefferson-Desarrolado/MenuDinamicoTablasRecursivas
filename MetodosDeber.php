<?php
require_once 'Conexion.php';

class MetodosDeber {
    private $pdo;

    public function __construct() {
        $conexion = new Conexion();
        $this->pdo = $conexion->conectar();
    }

    // Método para verificar el login
    public function verificarLogin($usuario, $clave) {
        $sql = "SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['usuario' => $usuario]);
        $usuarioData = $stmt->fetch();

        if ($usuarioData && password_verify($clave, $usuarioData['clave'])) {
            return $usuarioData;
        }

        return false;
    }

    // Método para cambiar la clave del usuario
    public function cambiarClave($usuarioId, $nuevaClave) {
        $claveHash = password_hash($nuevaClave, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET clave = :clave, cambio_clave = 0 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['clave' => $claveHash, 'id' => $usuarioId]);
    }

    // Obtener los menús del rol del usuario
    public function obtenerMenusPorRol($rolId) {
        $sql = "SELECT m.* FROM permisos p 
                JOIN menus m ON p.menu_id = m.id
                WHERE p.rol_id = :rolId
                ORDER BY m.parent_id, m.id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['rolId' => $rolId]);
        return $stmt->fetchAll();
    }

    // Método para obtener usuario por ID (opcional para sesiones)
    public function obtenerUsuarioPorId($id) {
        $sql = "SELECT * FROM usuarios WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
?>
