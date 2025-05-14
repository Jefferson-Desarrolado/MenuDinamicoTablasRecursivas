<?php
class Conexion {
    private $host = "localhost";    
    private $dbname = "sistema_seguridad"; // <- CORREGIDO
    private $user = "root";     
    private $password = ""; // XAMPP por defecto NO tiene contraseña
    private $charset = "utf8mb4";     
    private $pdo;

    public function conectar() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, $this->user, $this->password, $options);
            return $this->pdo;
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function cerrar() {
        $this->pdo = null;
    }
}
?>
