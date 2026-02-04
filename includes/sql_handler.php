<?php
require_once 'config/config.php';
require_once 'config/security.php';

// Инициализируем систему безопасности
$security = initSecurity();
$logger = $security['logger'];
class SQLHandler {
    private $logger; // Добавляем свойство для логгера
    private static $instance = null;
    private $pdo;
    private $prepared_statements = [];
    private $transaction_level = 0;
    private $debug_mode = false;
    private $query_log = [];

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);

            global $logger; // Получаем глобальный логгер
            $this->logger = $logger;

            $logger->log('Database connection established', 'INFO');
        } catch (PDOException $e) {
            $logger->log('Database connection failed', 'ERROR', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query($sql, $params = []) {
        try {
            $start_time = microtime(true);
            
            if (!isset($this->prepared_statements[$sql])) {
                $this->prepared_statements[$sql] = $this->pdo->prepare($sql);
            }
            
            $stmt = $this->prepared_statements[$sql];
            $stmt->execute($params);
            
            $end_time = microtime(true);
            $execution_time = round(($end_time - $start_time) * 1000, 2);
            
            if ($this->debug_mode) {
                $this->query_log[] = [
                    'sql' => $sql,
                    'params' => $params,
                    'time' => $execution_time
                ];
            }
            
            return $stmt;
        } catch (PDOException $e) {
            $logger->log('SQL query failed', 'ERROR', [
                'sql' => $sql,
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function insert($table, $data) {
        try {
            $fields = array_keys($data);
            $placeholders = array_fill(0, count($fields), '?');
            
            $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") 
                   VALUES (" . implode(', ', $placeholders) . ")";
            
            $this->query($sql, array_values($data));
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->log('Insert operation failed', 'ERROR', [
                'table' => $table,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function update($table, $data, $where, $where_params = []) {
        try {
            $set = array_map(function($field) {
                return "{$field} = ?";
            }, array_keys($data));
            
            $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE {$where}";
            
            $params = array_merge(array_values($data), $where_params);
            
            return $this->query($sql, $params)->rowCount();
        } catch (PDOException $e) {
            $this->logger->log('Update operation failed', 'ERROR', [
                'table' => $table,
                'data' => $data,
                'where' => $where,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function delete($table, $where, $params = []) {
        try {
            $sql = "DELETE FROM {$table} WHERE {$where}";
            return $this->query($sql, $params)->rowCount();
        } catch (PDOException $e) {
            $this->logger->log('Delete operation failed', 'ERROR', [
                'table' => $table,
                'where' => $where,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function beginTransaction() {
        try {
            if ($this->transaction_level === 0) {
                $this->pdo->beginTransaction();
            }
            $this->transaction_level++;

            $this->logger->log('Transaction started', 'INFO');
        } catch (PDOException $e) {
            $this->logger->log('Transaction start failed', 'ERROR', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function commit() {
        try {
            $this->transaction_level--;
            if ($this->transaction_level === 0) {
                $this->pdo->commit();
                $this->logger->log('Transaction committed', 'INFO');
            }
        } catch (PDOException $e) {
            $this->rollback();
            $this->logger->log('Transaction commit failed', 'ERROR', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function rollback() {
        try {
            if ($this->transaction_level > 0) {
                $this->pdo->rollBack();
                $this->transaction_level = 0;
                $this->logger->log('Transaction rolled back', 'INFO');
            }
        } catch (PDOException $e) {
            $this->logger->log('Transaction rollback failed', 'ERROR', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function quote($value) {
        return $this->pdo->quote($value);
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function rowCount() {
        return $this->pdo->rowCount();
    }

    public function setDebugMode($debug) {
        $this->debug_mode = $debug;
    }

    public function getQueryLog() {
        return $this->query_log;
    }

    public function clearQueryLog() {
        $this->query_log = [];
    }

    public function getPDO() {
        return $this->pdo;
    }

    public function __destruct() {
        $this->prepared_statements = [];
        $this->pdo = null;
    }
}

// Создаем глобальный экземпляр обработчика SQL
$sql_handler = SQLHandler::getInstance(); 