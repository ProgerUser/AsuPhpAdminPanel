<?php
// config/security.php
// Только классы и функции безопасности

// Функция для генерации CSRF токена
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Функция для проверки CSRF токена
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        http_response_code(403);
        die('CSRF token validation failed');
    }
    return true;
}

// Функция для безопасной регенерации сессии
function regenerateSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }
}

// Класс для защиты от брутфорс атак
class BruteForceProtection {
    private $db;
    private $attempts_table = 'login_attempts';
    private $max_attempts = 5;
    private $timeout_minutes = 15;

    public function __construct($db) {
        $this->db = $db;
        $this->createAttemptsTable();
    }

    private function createAttemptsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->attempts_table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            timestamp DATETIME NOT NULL,
            username VARCHAR(255) NOT NULL
        )";
        if (method_exists($this->db, 'rawQuery')) {
            $this->db->rawQuery($sql);
        } else if (method_exists($this->db, 'query')) {
            $this->db->query($sql);
        }
    }

    public function isBlocked($username, $ip) {
        $this->cleanOldAttempts();
        // WHERE (ip_address = ? OR username = ?) AND timestamp > DATE_SUB(NOW(), INTERVAL ? MINUTE)
        $this->db->where('(ip_address = ? OR username = ?)', [$ip, $username]);
        $this->db->where('timestamp > DATE_SUB(NOW(), INTERVAL ? MINUTE)', [$this->timeout_minutes]);
        $count = $this->db->getValue($this->attempts_table, 'COUNT(*)');
        return ((int)$count) >= $this->max_attempts;
    }

    public function addAttempt($username, $ip) {
        $this->db->insert($this->attempts_table, [
            'ip_address' => $ip,
            'username' => $username,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    private function cleanOldAttempts() {
        $this->db->where('timestamp < DATE_SUB(NOW(), INTERVAL ? MINUTE)', [$this->timeout_minutes]);
        $this->db->delete($this->attempts_table);
    }
}

// Класс для безопасного логирования
class SecurityLogger {
    private $log_file;

    public function __construct($log_file = null) {
        $this->log_file = $log_file ?: dirname(dirname(__FILE__)) . '/logs/security.log';
        $this->ensureLogDirectoryExists();
    }

    private function ensureLogDirectoryExists() {
        $dir = dirname($this->log_file);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public function log($event, $level = 'INFO', $context = []) {
        $date = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user = $_SESSION['user_name'] ?? 'anonymous';

        $contextStr = !empty($context) ? json_encode($context) : '';
        $message = "[$date][$level] IP: $ip, User: $user, Event: $event $contextStr\n";

        file_put_contents($this->log_file, $message, FILE_APPEND | LOCK_EX);
    }
}

// Функция для инициализации системы безопасности
function initSecurity() {
    if (!function_exists('getDbInstance')) {
        require_once dirname(__FILE__) . '/config.php';
    }

    $db = getDbInstance();
    $logger = new SecurityLogger();
    $bruteforce = new BruteForceProtection($db);

    return [
        'logger' => $logger,
        'bruteforce' => $bruteforce,
        'db' => $db
    ];
}
?>