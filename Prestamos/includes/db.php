<?php
/**
 * =====================================================
 * CONFIGURACIÓN DE CONEXIÓN A BASE DE DATOS
 * db.php
 * =====================================================
 */

// Prevenir acceso directo
if (!defined('SISTEMA_CARGADO')) {
    define('SISTEMA_CARGADO', true);
}

// =====================================================
// CONFIGURACIÓN DE CONEXIÓN
// =====================================================

// Configuración para PRODUCCIÓN (Donweb)
define('DB_HOST', 'localhost');          // o la IP del servidor MySQL
define('DB_NAME', 'sistema_prestamos');  // Nombre de tu base de datos
define('DB_USER', 'tu_usuario');         // Tu usuario de MySQL
define('DB_PASS', 'tu_contraseña');      // Tu contraseña de MySQL
define('DB_CHARSET', 'utf8mb4');

// Configuración para DESARROLLO (comentar en producción)
/*
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_prestamos');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
*/

// =====================================================
// ZONA HORARIA
// =====================================================
date_default_timezone_set('America/Argentina/Buenos_Aires');

// =====================================================
// MANEJO DE ERRORES
// =====================================================
// En PRODUCCIÓN: comentar o establecer en 0
error_reporting(E_ALL);
ini_set('display_errors', 1);

// En PRODUCCIÓN: descomentar
/*
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');
*/

// =====================================================
// CLASE DE CONEXIÓN A BASE DE DATOS
// =====================================================

class Database {
    private static $instance = null;
    private $conn;
    private $error;
    
    /**
     * Constructor privado (Patrón Singleton)
     */
    private function __construct() {
        $this->conectar();
    }
    
    /**
     * Obtener instancia única de la conexión
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establecer conexión con la base de datos
     */
    private function conectar() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true, // Conexión persistente
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            $this->registrarError($e);
            die("Error de conexión: " . $this->getMensajeError());
        }
    }
    
    /**
     * Obtener la conexión
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Ejecutar query preparada
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->registrarError($e);
            throw $e;
        }
    }
    
    /**
     * Ejecutar SELECT y obtener todos los resultados
     */
    public function select($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Ejecutar SELECT y obtener un solo resultado
     */
    public function selectOne($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Ejecutar INSERT
     */
    public function insert($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Ejecutar UPDATE
     */
    public function update($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Ejecutar DELETE
     */
    public function delete($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Contar registros
     */
    public function count($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Iniciar transacción
     */
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    /**
     * Confirmar transacción
     */
    public function commit() {
        return $this->conn->commit();
    }
    
    /**
     * Revertir transacción
     */
    public function rollback() {
        return $this->conn->rollBack();
    }
    
    /**
     * Verificar si hay una transacción activa
     */
    public function inTransaction() {
        return $this->conn->inTransaction();
    }
    
    /**
     * Escapar valores para prevenir SQL Injection (uso adicional)
     */
    public function escape($value) {
        return $this->conn->quote($value);
    }
    
    /**
     * Obtener el último ID insertado
     */
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    /**
     * Verificar conexión
     */
    public function isConnected() {
        return $this->conn !== null;
    }
    
    /**
     * Registrar error en base de datos
     */
    private function registrarError($exception) {
        try {
            // Intentar registrar en tabla logs_sistema
            $sql = "INSERT INTO logs_sistema (nivel, categoria, mensaje, stack_trace, fecha_log) 
                    VALUES ('ERROR', 'DATABASE', ?, ?, NOW())";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $exception->getMessage(),
                $exception->getTraceAsString()
            ]);
        } catch (Exception $e) {
            // Si falla el registro en BD, registrar en archivo
            error_log("Error DB: " . $exception->getMessage());
        }
    }
    
    /**
     * Obtener mensaje de error amigable
     */
    private function getMensajeError() {
        // En producción, no mostrar detalles técnicos
        if (defined('PRODUCCION') && PRODUCCION === true) {
            return "Error al conectar con la base de datos. Por favor, contacte al administrador.";
        }
        return $this->error;
    }
    
    /**
     * Cerrar conexión
     */
    public function close() {
        $this->conn = null;
    }
    
    /**
     * Prevenir clonación
     */
    private function __clone() {}
    
    /**
     * Prevenir deserialización
     */
    public function __wakeup() {
        throw new Exception("No se puede deserializar singleton");
    }
}

// =====================================================
// FUNCIONES HELPER GLOBALES
// =====================================================

/**
 * Obtener instancia de la base de datos
 */
function getDB() {
    return Database::getInstance();
}

/**
 * Obtener conexión PDO directa
 */
function getConnection() {
    return Database::getInstance()->getConnection();
}

/**
 * Ejecutar query simple
 */
function dbQuery($sql, $params = []) {
    return Database::getInstance()->query($sql, $params);
}

/**
 * Ejecutar SELECT
 */
function dbSelect($sql, $params = []) {
    return Database::getInstance()->select($sql, $params);
}

/**
 * Ejecutar SELECT (un solo registro)
 */
function dbSelectOne($sql, $params = []) {
    return Database::getInstance()->selectOne($sql, $params);
}

/**
 * Ejecutar INSERT
 */
function dbInsert($sql, $params = []) {
    return Database::getInstance()->insert($sql, $params);
}

/**
 * Ejecutar UPDATE
 */
function dbUpdate($sql, $params = []) {
    return Database::getInstance()->update($sql, $params);
}

/**
 * Ejecutar DELETE
 */
function dbDelete($sql, $params = []) {
    return Database::getInstance()->delete($sql, $params);
}

/**
 * Contar registros
 */
function dbCount($sql, $params = []) {
    return Database::getInstance()->count($sql, $params);
}

// =====================================================
// VERIFICAR CONEXIÓN AL CARGAR
// =====================================================

try {
    $db = Database::getInstance();
    if (!$db->isConnected()) {
        throw new Exception("No se pudo establecer la conexión con la base de datos");
    }
} catch (Exception $e) {
    die("Error crítico: " . $e->getMessage());
}

// =====================================================
// CONSTANTES ADICIONALES DEL SISTEMA
// =====================================================

// Definir si estamos en producción
define('PRODUCCION', false); // Cambiar a true en servidor de producción

// URL base del sistema
define('BASE_URL', 'http://localhost/sistema_prestamos/'); // Cambiar en producción

// Directorios
define('ROOT_PATH', __DIR__ . '/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('LOGS_PATH', ROOT_PATH . 'logs/');

// Crear directorios si no existen
if (!file_exists(UPLOADS_PATH)) {
    mkdir(UPLOADS_PATH, 0755, true);
}
if (!file_exists(LOGS_PATH)) {
    mkdir(LOGS_PATH, 0755, true);
}

?>