<?php
require_once 'config/config.php';
require_once 'config/security.php';

class CSRFHandler {
    private static $instance = null;
    private $token_length = 32;
    private $token_name = 'csrf_token';
    private $token_expire = 3600; // 1 час
    private $token_storage = 'session';
    private $token_salt = '';
    private $token_algorithm = 'sha256';

    private function __construct() {
        $this->token_salt = bin2hex(random_bytes(16));
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function generateToken() {
        try {
            $token = bin2hex(random_bytes($this->token_length));
            $hash = hash_hmac($this->token_algorithm, $token, $this->token_salt);
            
            $token_data = [
                'token' => $token,
                'hash' => $hash,
                'expire' => time() + $this->token_expire
            ];
            
            $this->storeToken($token_data);
            
            return $token;
        } catch (Exception $e) {
            $logger->log('Token generation failed', 'ERROR', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function validateToken($token) {
        try {
            $stored_data = $this->getStoredToken();
            
            if (!$stored_data) {
                throw new Exception('No token found');
            }
            
            if (time() > $stored_data['expire']) {
                $this->removeToken();
                throw new Exception('Token expired');
            }
            
            $hash = hash_hmac($this->token_algorithm, $token, $this->token_salt);
            
            if (!hash_equals($hash, $stored_data['hash'])) {
                throw new Exception('Invalid token');
            }
            
            return true;
        } catch (Exception $e) {
            $logger->log('Token validation failed', 'ERROR', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getTokenField() {
        return '<input type="hidden" name="' . $this->token_name . '" value="' . $this->generateToken() . '">';
    }

    public function getTokenName() {
        return $this->token_name;
    }

    private function storeToken($data) {
        try {
            if ($this->token_storage === 'session') {
                $_SESSION[$this->token_name] = $data;
            } else if ($this->token_storage === 'cookie') {
                setcookie(
                    $this->token_name,
                    json_encode($data),
                    [
                        'expires' => $data['expire'],
                        'path' => '/',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]
                );
            }
        } catch (Exception $e) {
            $logger->log('Token storage failed', 'ERROR', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function getStoredToken() {
        try {
            if ($this->token_storage === 'session') {
                return $_SESSION[$this->token_name] ?? null;
            } else if ($this->token_storage === 'cookie') {
                return isset($_COOKIE[$this->token_name]) ? json_decode($_COOKIE[$this->token_name], true) : null;
            }
            
            return null;
        } catch (Exception $e) {
            $logger->log('Token retrieval failed', 'ERROR', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function removeToken() {
        try {
            if ($this->token_storage === 'session') {
                unset($_SESSION[$this->token_name]);
            } else if ($this->token_storage === 'cookie') {
                setcookie(
                    $this->token_name,
                    '',
                    [
                        'expires' => time() - 3600,
                        'path' => '/'
                    ]
                );
            }
        } catch (Exception $e) {
            $logger->log('Token removal failed', 'ERROR', [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function setTokenLength($length) {
        $this->token_length = $length;
    }

    public function setTokenName($name) {
        $this->token_name = $name;
    }

    public function setTokenExpire($expire) {
        $this->token_expire = $expire;
    }

    public function setTokenStorage($storage) {
        if (!in_array($storage, ['session', 'cookie'])) {
            throw new Exception('Invalid storage type');
        }
        $this->token_storage = $storage;
    }

    public function setTokenSalt($salt) {
        $this->token_salt = $salt;
    }

    public function setTokenAlgorithm($algorithm) {
        if (!in_array($algorithm, hash_algos())) {
            throw new Exception('Invalid hash algorithm');
        }
        $this->token_algorithm = $algorithm;
    }

    public function getTokenLength() {
        return $this->token_length;
    }

    public function getTokenName() {
        return $this->token_name;
    }

    public function getTokenExpire() {
        return $this->token_expire;
    }

    public function getTokenStorage() {
        return $this->token_storage;
    }

    public function getTokenSalt() {
        return $this->token_salt;
    }

    public function getTokenAlgorithm() {
        return $this->token_algorithm;
    }

    public function requireToken() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST[$this->token_name])) {
                $logger->log('CSRF token missing', 'ERROR');
                http_response_code(403);
                die('Invalid request');
            }
            
            if (!$this->validateToken($_POST[$this->token_name])) {
                $logger->log('CSRF token validation failed', 'ERROR');
                http_response_code(403);
                die('Invalid request');
            }
        }
    }
}

// Создаем глобальный экземпляр обработчика CSRF
$csrf_handler = CSRFHandler::getInstance(); 