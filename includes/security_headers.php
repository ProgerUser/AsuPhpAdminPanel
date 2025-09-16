<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/security.php';

class SecurityHeaders {
    private static $instance = null;
    private $headers = [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self';",
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=(), payment=(), usb=(), fullscreen=(self), interest-cohort=()',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
        'Expect-CT' => 'max-age=86400, enforce',
        'Cross-Origin-Embedder-Policy' => 'require-corp',
        'Cross-Origin-Opener-Policy' => 'same-origin',
        'Cross-Origin-Resource-Policy' => 'same-origin'
    ];
    private $enabled = true;

    private function __construct() {
        if ($this->enabled) {
            $this->sendHeaders();
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function sendHeaders() {
        try {
            foreach ($this->headers as $name => $value) {
                header("$name: $value");
            }

            // Удаление заголовков, которые могут раскрыть информацию
            header_remove('X-Powered-By');
            header_remove('Server');
            header_remove('X-AspNet-Version');
            header_remove('X-AspNetMvc-Version');

            // Логгер не обязателен; избегаем использования неинициализированных переменных
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function setHeader($name, $value) {
        $this->headers[$name] = $value;
        if ($this->enabled) {
            header("$name: $value");
        }
    }

    public function removeHeader($name) {
        if (isset($this->headers[$name])) {
            unset($this->headers[$name]);
            if ($this->enabled) {
                header_remove($name);
            }
        }
    }

    public function setCSP($directives) {
        $csp = [];
        foreach ($directives as $directive => $value) {
            $csp[] = "$directive $value";
        }
        $this->setHeader('Content-Security-Policy', implode('; ', $csp));
    }

    public function setHSTS($max_age = 31536000, $include_subdomains = true, $preload = true) {
        $value = "max-age=$max_age";
        if ($include_subdomains) {
            $value .= '; includeSubDomains';
        }
        if ($preload) {
            $value .= '; preload';
        }
        $this->setHeader('Strict-Transport-Security', $value);
    }

    public function setPermissionsPolicy($permissions) {
        $value = implode(', ', array_map(
            function($permission, $value) {
                return "$permission=($value)";
            },
            array_keys($permissions),
            array_values($permissions)
        ));
        $this->setHeader('Permissions-Policy', $value);
    }

    public function setReferrerPolicy($policy) {
        $this->setHeader('Referrer-Policy', $policy);
    }

    public function setXFrameOptions($option) {
        $this->setHeader('X-Frame-Options', $option);
    }

    public function setXSSProtection($value) {
        $this->setHeader('X-XSS-Protection', $value);
    }

    public function setContentTypeOptions($value) {
        $this->setHeader('X-Content-Type-Options', $value);
    }

    public function setExpectCT($max_age = 86400, $enforce = true) {
        $value = "max-age=$max_age";
        if ($enforce) {
            $value .= ', enforce';
        }
        $this->setHeader('Expect-CT', $value);
    }

    public function setCrossOriginEmbedderPolicy($value) {
        $this->setHeader('Cross-Origin-Embedder-Policy', $value);
    }

    public function setCrossOriginOpenerPolicy($value) {
        $this->setHeader('Cross-Origin-Opener-Policy', $value);
    }

    public function setCrossOriginResourcePolicy($value) {
        $this->setHeader('Cross-Origin-Resource-Policy', $value);
    }

    public function enable() {
        $this->enabled = true;
        $this->sendHeaders();
    }

    public function disable() {
        $this->enabled = false;
        foreach ($this->headers as $name => $value) {
            header_remove($name);
        }
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function isEnabled() {
        return $this->enabled;
    }

    public function setDefaultHeaders() {
        $this->headers = [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self';",
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=(), payment=(), usb=(), fullscreen=(self), interest-cohort=()',
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
            'Expect-CT' => 'max-age=86400, enforce',
            'Cross-Origin-Embedder-Policy' => 'require-corp',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Cross-Origin-Resource-Policy' => 'same-origin'
        ];
        if ($this->enabled) {
            $this->sendHeaders();
        }
    }
}

// Создаем глобальный экземпляр обработчика заголовков безопасности
$security_headers = SecurityHeaders::getInstance(); 