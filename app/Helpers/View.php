<?php

namespace App\Helpers;

class View
{
    private static array $shared = [];

    /** Compartilha variáveis com todas as views */
    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    /** Renderiza uma view dentro de um layout */
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $data = array_merge(self::$shared, $data);

        // Captura o conteúdo da view
        $content = self::capture($view, $data);

        // Renderiza no layout
        if ($layout) {
            extract($data);
            require VIEW_PATH . "/layouts/{$layout}.php";
        } else {
            echo $content;
        }
    }

    /** Renderiza sem layout (para partials, JSON, etc.) */
    public static function partial(string $view, array $data = []): void
    {
        $data = array_merge(self::$shared, $data);
        extract($data);
        require VIEW_PATH . "/{$view}.php";
    }

    /** Captura output de uma view como string */
    public static function capture(string $view, array $data = []): string
    {
        $data = array_merge(self::$shared, $data);
        extract($data);
        ob_start();
        require VIEW_PATH . "/{$view}.php";
        return ob_get_clean();
    }

    /** Redireciona */
    public static function redirect(string $url, int $code = 302): void
    {
        http_response_code($code);
        header("Location: {$url}");
        exit;
    }

    /** JSON response */
    public static function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /** Escapa output HTML */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
