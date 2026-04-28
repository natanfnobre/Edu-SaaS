<?php

namespace App\Helpers;

class Upload
{
    private array $config;
    private string $basePath;

    public function __construct()
    {
        $this->config   = require CONFIG_PATH . '/app.php';
        $this->basePath = $this->config['upload']['path'];

        // Garante que o diretório existe
        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0755, true);
        }
    }

    /**
     * Upload de imagem (jpg, png, webp)
     */
    public function image(array $file, string $subdir = ''): ?string
    {
        if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Valida tipo
        if (!in_array($file['type'], $this->config['upload']['allowed_images'])) {
            return null;
        }

        // Valida tamanho
        if ($file['size'] > $this->config['upload']['max_size']) {
            return null;
        }

        return $this->save($file, $subdir);
    }

    /**
     * Upload de documento (pdf, jpg, png)
     */
    public function document(array $file, string $subdir = ''): ?string
    {
        if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if (!in_array($file['type'], $this->config['upload']['allowed_docs'])) {
            return null;
        }

        if ($file['size'] > $this->config['upload']['max_size']) {
            return null;
        }

        return $this->save($file, $subdir);
    }

    /**
     * Salva o arquivo no disco
     */
    private function save(array $file, string $subdir): ?string
    {
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('upload_', true) . '.' . $ext;
        
        $dir  = $this->basePath . ($subdir ? '/' . $subdir : '');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fullPath = $dir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            // Retorna caminho relativo (sem o basePath)
            return ($subdir ? $subdir . '/' : '') . $filename;
        }

        return null;
    }

    /**
     * Remove um arquivo do disco
     */
    public function delete(string $path): bool
    {
        $fullPath = $this->basePath . '/' . $path;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }

    /**
     * Retorna URL pública do upload
     */
    public function url(string $path): string
    {
        return asset('uploads/' . $path);
    }
}
