<?php
require_once 'config/config.php';
require_once 'config/security.php';

$security = initSecurity();
$logger = $security['logger'];

class RateLimiter {
    private static $instance = null;
    private $limits = [
        'default' => [
            'requests' => 100,
            'period' => 60 // 1 минута
        ],
        'login' => [
            'requests' => 5,
            'period' => 300 // 5 минут
        ],
        'api' => [
            'requests' => 1000,
            'period' => 3600 // 1 час
        ]
    ];
    private $storage = [];
    private $cleanup_interval = 3600; // 1 час
    private $last_cleanup = 0;

    private function __construct() {
        global $logger; // Получаем глобальный логгер
        $this->logger = $logger;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function check($key, $type = 'default') {
        try {
            // Очистка устаревших записей
            $this->cleanup();

            // Получение лимитов для типа
            $limit = $this->getLimit($type);

            // Получение текущего времени
            $now = time();

            // Инициализация ключа
            if (!isset($this->storage[$key])) {
                $this->storage[$key] = [
                    'requests' => [],
                    'type' => $type
                ];
            }

            // Удаление устаревших запросов
            $this->storage[$key]['requests'] = array_filter(
                $this->storage[$key]['requests'],
                function($timestamp) use ($now, $limit) {
                    return $timestamp > ($now - $limit['period']);
                }
            );

            // Проверка количества запросов
            if (count($this->storage[$key]['requests']) >= $limit['requests']) {
                $this->logger->log('Rate limit exceeded', 'WARNING', [
                    'key' => $key,
                    'type' => $type,
                    'requests' => count($this->storage[$key]['requests'])
                ]);
                return false;
            }

            // Добавление нового запроса
            $this->storage[$key]['requests'][] = $now;

            return true;
        } catch (Exception $e) {
            $this->logger->log('Rate limit check failed', 'ERROR', [
                'error' => $e->getMessage(),
                'key' => $key,
                'type' => $type
            ]);
            throw $e;
        }
    }

    public function getRemaining($key, $type = 'default') {
        try {
            // Получение лимитов для типа
            $limit = $this->getLimit($type);

            // Получение текущего времени
            $now = time();

            // Проверка наличия ключа
            if (!isset($this->storage[$key])) {
                return $limit['requests'];
            }

            // Удаление устаревших запросов
            $this->storage[$key]['requests'] = array_filter(
                $this->storage[$key]['requests'],
                function($timestamp) use ($now, $limit) {
                    return $timestamp > ($now - $limit['period']);
                }
            );

            // Вычисление оставшихся запросов
            $remaining = $limit['requests'] - count($this->storage[$key]['requests']);

            return max(0, $remaining);
        } catch (Exception $e) {
            $logger->log('Get remaining requests failed', 'ERROR', [
                'error' => $e->getMessage(),
                'key' => $key,
                'type' => $type
            ]);
            throw $e;
        }
    }

    public function reset($key) {
        try {
            if (isset($this->storage[$key])) {
                unset($this->storage[$key]);
                $this->logger->log('Rate limit reset', 'INFO', ['key' => $key]);
            }
        } catch (Exception $e) {
            $this->logger->log('Rate limit reset failed', 'ERROR', [
                'error' => $e->getMessage(),
                'key' => $key
            ]);
            throw $e;
        }
    }

    private function getLimit($type) {
        return $this->limits[$type] ?? $this->limits['default'];
    }

    private function cleanup() {
        $now = time();

        // Проверка необходимости очистки
        if ($now - $this->last_cleanup < $this->cleanup_interval) {
            return;
        }

        try {
            // Удаление устаревших записей
            foreach ($this->storage as $key => $data) {
                $limit = $this->getLimit($data['type']);
                $this->storage[$key]['requests'] = array_filter(
                    $data['requests'],
                    function($timestamp) use ($now, $limit) {
                        return $timestamp > ($now - $limit['period']);
                    }
                );

                // Удаление пустых записей
                if (empty($this->storage[$key]['requests'])) {
                    unset($this->storage[$key]);
                }
            }

            $this->last_cleanup = $now;

            $this->logger->log('Rate limit storage cleaned up', 'INFO', [
                'entries' => count($this->storage)
            ]);
        } catch (Exception $e) {
            $this->logger->log('Rate limit cleanup failed', 'ERROR', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function setLimit($type, $requests, $period) {
        $this->limits[$type] = [
            'requests' => $requests,
            'period' => $period
        ];
    }

    public function setCleanupInterval($interval) {
        $this->cleanup_interval = $interval;
    }

    public function getLimits() {
        return $this->limits;
    }

    public function getStorage() {
        return $this->storage;
    }

    public function clearStorage() {
        $this->storage = [];
        $this->last_cleanup = 0;
    }

    public function getKey($type = 'default') {
        // Генерация ключа на основе IP и типа
        $ip = $_SERVER['REMOTE_ADDR'];
        return md5($ip . ':' . $type);
    }

    public function requireLimit($type = 'default') {
        $key = $this->getKey($type);

        if (!$this->check($key, $type)) {
            http_response_code(429);
            header('Content-Type: application/json');
            header('Retry-After: ' . $this->getRetryAfter($key, $type));
            
            echo json_encode([
                'error' => 'Too Many Requests',
                'message' => 'Пожалуйста, подождите перед следующей попыткой.',
                'retry_after' => $this->getRetryAfter($key, $type)
            ]);
            
            exit();
        }
    }

    private function getRetryAfter($key, $type) {
        if (!isset($this->storage[$key])) {
            return 0;
        }

        $limit = $this->getLimit($type);
        $oldest_request = min($this->storage[$key]['requests']);
        $retry_after = ($oldest_request + $limit['period']) - time();

        return max(0, $retry_after);
    }
}

// Создаем глобальный экземпляр обработчика rate limiting
$rate_limiter = RateLimiter::getInstance(); 