<?php
require_once dirname(__DIR__) . '/config/session_init.php'; // Сначала настройки сессии
require_once dirname(__DIR__) . '/config/config.php';       // Потом конфиг
require_once dirname(__DIR__) . '/config/security.php';     // Потом безопасность

// Инициализируем систему безопасности
$security = initSecurity();
$logger = $security['logger'];

class XSSHandler {
    private static $instance = null;
    private $encoding = 'UTF-8';
    private $html_entities = true;
    private $strip_tags = false;
    private $allowed_tags = '';
    private $allowed_attributes = [
        'a' => ['href', 'title', 'target'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'p' => ['class'],
        'div' => ['class', 'id'],
        'span' => ['class'],
        'table' => ['class', 'width'],
        'tr' => ['class'],
        'td' => ['class', 'colspan', 'rowspan'],
        'th' => ['class', 'colspan', 'rowspan']
    ];
    private $logger; // Добавляем свойство для логгера

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

    public function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }

        if (is_string($data)) {
            if ($this->strip_tags) {
                $data = strip_tags($data, $this->allowed_tags);
            }

            if ($this->html_entities) {
                $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, $this->encoding);
            }
        }

        return $data;
    }

    public function sanitizeHTML($html) {
        try {
            $dom = new DOMDocument();
            $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', $this->encoding), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            $xpath = new DOMXPath($dom);
            $nodes = $xpath->query('//*');

            foreach ($nodes as $node) {
                if ($node instanceof DOMElement) {
                    // Удаляем неразрешенные атрибуты
                    $attributes = $node->attributes;
                    for ($i = $attributes->length - 1; $i >= 0; $i--) {
                        $attr = $attributes->item($i);
                        if (!$this->isAllowedAttribute($node->tagName, $attr->name)) {
                            $node->removeAttribute($attr->name);
                        }
                    }

                    // Удаляем неразрешенные теги
                    if (!$this->isAllowedTag($node->tagName)) {
                        $node->parentNode->replaceChild(
                            $dom->createTextNode($node->textContent),
                            $node
                        );
                    }
                }
            }

            $html = $dom->saveHTML();
            return $html;
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log('HTML sanitization failed', 'ERROR', [
                    'error' => $e->getMessage()
                ]);
            }
            return $this->sanitize($html);
        }
    }

    public function sanitizeURL($url) {
        try {
            $url = filter_var($url, FILTER_SANITIZE_URL);

            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                throw new Exception('Invalid URL');
            }

            return $url;
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log('URL sanitization failed', 'ERROR', [
                    'url' => $url,
                    'error' => $e->getMessage()
                ]);
            }
            return '';
        }
    }

    public function sanitizeEmail($email) {
        try {
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);

            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                throw new Exception('Invalid email');
            }

            return $email;
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log('Email sanitization failed', 'ERROR', [
                    'email' => $email,
                    'error' => $e->getMessage()
                ]);
            }
            return '';
        }
    }

    public function sanitizeInteger($value) {
        try {
            $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);

            if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                throw new Exception('Invalid integer');
            }

            return $value;
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log('Integer sanitization failed', 'ERROR', [
                    'value' => $value,
                    'error' => $e->getMessage()
                ]);
            }
            return 0;
        }
    }

    public function sanitizeFloat($value) {
        try {
            $value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

            if (filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
                throw new Exception('Invalid float');
            }

            return $value;
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log('Float sanitization failed', 'ERROR', [
                    'value' => $value,
                    'error' => $e->getMessage()
                ]);
            }
            return 0.0;
        }
    }

    public function sanitizeDate($date, $format = 'Y-m-d') {
        try {
            $timestamp = strtotime($date);

            if ($timestamp === false) {
                throw new Exception('Invalid date');
            }

            return date($format, $timestamp);
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log('Date sanitization failed', 'ERROR', [
                    'date' => $date,
                    'error' => $e->getMessage()
                ]);
            }
            return date($format);
        }
    }

    public function sanitizePhone($phone) {
        try {
            $phone = preg_replace('/[^0-9+]/', '', $phone);

            if (strlen($phone) < 10) {
                throw new Exception('Invalid phone number');
            }

            return $phone;
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log('Phone sanitization failed', 'ERROR', [
                    'phone' => $phone,
                    'error' => $e->getMessage()
                ]);
            }
            return '';
        }
    }

    private function isAllowedTag($tag) {
        return in_array(strtolower($tag), array_keys($this->allowed_attributes));
    }

    private function isAllowedAttribute($tag, $attribute) {
        $tag = strtolower($tag);
        $attribute = strtolower($attribute);

        if (!isset($this->allowed_attributes[$tag])) {
            return false;
        }

        return in_array($attribute, $this->allowed_attributes[$tag]);
    }

    public function setEncoding($encoding) {
        $this->encoding = $encoding;
    }

    public function setHTMLEntities($html_entities) {
        $this->html_entities = $html_entities;
    }

    public function setStripTags($strip_tags) {
        $this->strip_tags = $strip_tags;
    }

    public function setAllowedTags($allowed_tags) {
        $this->allowed_tags = $allowed_tags;
    }

    public function setAllowedAttributes($allowed_attributes) {
        $this->allowed_attributes = $allowed_attributes;
    }

    public function getEncoding() {
        return $this->encoding;
    }

    public function isHTMLEntities() {
        return $this->html_entities;
    }

    public function isStripTags() {
        return $this->strip_tags;
    }

    public function getAllowedTags() {
        return $this->allowed_tags;
    }

    public function getAllowedAttributes() {
        return $this->allowed_attributes;
    }
}

// Создаем глобальный экземпляр обработчика XSS
$xss_handler = XSSHandler::getInstance();