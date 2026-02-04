<?php
require_once 'config/config.php';
require_once 'config/security.php';

// Инициализируем систему безопасности
$security = initSecurity();
$logger = $security['logger'];

class FileHandler {
    private static $instance = null;
    private $allowed_types = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    private $max_file_size = 5242880; // 5MB
    private $upload_dir = 'uploads/';
    private $temp_dir = 'temp/';
    private $secure_filenames = true;

    private function __construct() {
        $this->createDirectories();
        global $logger; // Получаем глобальный логгер
        $this->logger = $logger;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function createDirectories() {
        try {
            if (!file_exists($this->upload_dir)) {
                mkdir($this->upload_dir, 0755, true);
            }
            if (!file_exists($this->temp_dir)) {
                mkdir($this->temp_dir, 0755, true);
            }
        } catch (Exception $e) {
            $logger->log('Failed to create directories', 'ERROR', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function upload($file, $subdir = '') {
        try {
            if (!$this->validateFile($file)) {
                throw new Exception('Invalid file');
            }

            $filename = $this->generateSecureFilename($file['name']);
            $upload_path = $this->upload_dir . $subdir . '/' . $filename;
            
            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                throw new Exception('Failed to move uploaded file');
            }

            chmod($upload_path, 0644);

            $this->logger->log('File uploaded successfully', 'INFO', [
                'filename' => $filename,
                'path' => $upload_path
            ]);

            return $filename;
        } catch (Exception $e) {
            $this->logger->log('File upload failed', 'ERROR', [
                'error' => $e->getMessage(),
                'file' => $file['name']
            ]);
            throw $e;
        }
    }

    public function delete($filename, $subdir = '') {
        try {
            $file_path = $this->upload_dir . $subdir . '/' . $filename;
            
            if (!file_exists($file_path)) {
                throw new Exception('File not found');
            }

            if (!unlink($file_path)) {
                throw new Exception('Failed to delete file');
            }

            $this->logger->log('File deleted successfully', 'INFO', [
                'filename' => $filename,
                'path' => $file_path
            ]);

            return true;
        } catch (Exception $e) {
            $this->logger->log('File deletion failed', 'ERROR', [
                'error' => $e->getMessage(),
                'filename' => $filename
            ]);
            throw $e;
        }
    }

    public function move($source, $destination, $subdir = '') {
        try {
            $source_path = $this->upload_dir . $subdir . '/' . $source;
            $dest_path = $this->upload_dir . $subdir . '/' . $destination;

            if (!file_exists($source_path)) {
                throw new Exception('Source file not found');
            }

            if (!rename($source_path, $dest_path)) {
                throw new Exception('Failed to move file');
            }

            $this->logger->log('File moved successfully', 'INFO', [
                'source' => $source,
                'destination' => $destination
            ]);

            return true;
        } catch (Exception $e) {
            $this->logger->log('File move failed', 'ERROR', [
                'error' => $e->getMessage(),
                'source' => $source,
                'destination' => $destination
            ]);
            throw $e;
        }
    }

    public function copy($source, $destination, $subdir = '') {
        try {
            $source_path = $this->upload_dir . $subdir . '/' . $source;
            $dest_path = $this->upload_dir . $subdir . '/' . $destination;

            if (!file_exists($source_path)) {
                throw new Exception('Source file not found');
            }

            if (!copy($source_path, $dest_path)) {
                throw new Exception('Failed to copy file');
            }

            chmod($dest_path, 0644);

            $this->logger->log('File copied successfully', 'INFO', [
                'source' => $source,
                'destination' => $destination
            ]);

            return true;
        } catch (Exception $e) {
            $this->logger->log('File copy failed', 'ERROR', [
                'error' => $e->getMessage(),
                'source' => $source,
                'destination' => $destination
            ]);
            throw $e;
        }
    }

    private function validateFile($file) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception('Invalid file upload');
        }

        if ($file['size'] > $this->max_file_size) {
            throw new Exception('File too large');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $this->allowed_types)) {
            throw new Exception('Invalid file type');
        }

        return true;
    }

    private function generateSecureFilename($original_name) {
        if (!$this->secure_filenames) {
            return $original_name;
        }

        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        
        return $filename;
    }

    public function setAllowedTypes($types) {
        $this->allowed_types = $types;
    }

    public function setMaxFileSize($size) {
        $this->max_file_size = $size;
    }

    public function setUploadDir($dir) {
        $this->upload_dir = $dir;
        $this->createDirectories();
    }

    public function setTempDir($dir) {
        $this->temp_dir = $dir;
        $this->createDirectories();
    }

    public function setSecureFilenames($secure) {
        $this->secure_filenames = $secure;
    }

    public function getAllowedTypes() {
        return $this->allowed_types;
    }

    public function getMaxFileSize() {
        return $this->max_file_size;
    }

    public function getUploadDir() {
        return $this->upload_dir;
    }

    public function getTempDir() {
        return $this->temp_dir;
    }

    public function isSecureFilenames() {
        return $this->secure_filenames;
    }

    public function scanDirectory($dir) {
        try {
            $files = [];
            $path = $this->upload_dir . $dir;
            
            if (!is_dir($path)) {
                throw new Exception('Directory not found');
            }

            $items = scandir($path);
            foreach ($items as $item) {
                if ($item != '.' && $item != '..') {
                    $file_path = $path . '/' . $item;
                    if (is_file($file_path)) {
                        $files[] = [
                            'name' => $item,
                            'size' => filesize($file_path),
                            'type' => mime_content_type($file_path),
                            'modified' => filemtime($file_path)
                        ];
                    }
                }
            }

            return $files;
        } catch (Exception $e) {
            $this->logger->log('Directory scan failed', 'ERROR', [
                'error' => $e->getMessage(),
                'directory' => $dir
            ]);
            throw $e;
        }
    }

    public function createTempFile($content, $extension = 'tmp') {
        try {
            $filename = bin2hex(random_bytes(16)) . '.' . $extension;
            $filepath = $this->temp_dir . $filename;
            
            if (file_put_contents($filepath, $content) === false) {
                throw new Exception('Failed to create temporary file');
            }

            chmod($filepath, 0644);

            return $filename;
        } catch (Exception $e) {
            $this->logger->log('Temporary file creation failed', 'ERROR', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function cleanupTempFiles($max_age = 3600) {
        try {
            $files = scandir($this->temp_dir);
            $now = time();
            
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $filepath = $this->temp_dir . $file;
                    if (is_file($filepath)) {
                        if ($now - filemtime($filepath) > $max_age) {
                            unlink($filepath);
                        }
                    }
                }
            }

            $this->logger->log('Temporary files cleaned up', 'INFO');
        } catch (Exception $e) {
            $this->logger->log('Temporary files cleanup failed', 'ERROR', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

// Создаем глобальный экземпляр обработчика файлов
$file_handler = FileHandler::getInstance(); 