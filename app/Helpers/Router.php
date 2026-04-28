<?php

namespace App\Helpers;

class Router
{
    private static array $routes    = [];
    private static array $namedRoutes = [];
    private static array $middleware  = [];
    private static string $prefix    = '';

    // ── Registro de rotas ─────────────────────────────────────────

    public static function get(string $path, array|string $handler, string $name = ''): void
    {
        self::add('GET', $path, $handler, $name);
    }

    public static function post(string $path, array|string $handler, string $name = ''): void
    {
        self::add('POST', $path, $handler, $name);
    }

    public static function put(string $path, array|string $handler, string $name = ''): void
    {
        self::add('PUT', $path, $handler, $name);
    }

    public static function delete(string $path, array|string $handler, string $name = ''): void
    {
        self::add('DELETE', $path, $handler, $name);
    }

    public static function group(string $prefix, array $middleware, callable $callback): void
    {
        $previousPrefix     = self::$prefix;
        $previousMiddleware = self::$middleware;

        self::$prefix     = $previousPrefix . $prefix;
        self::$middleware = array_merge($previousMiddleware, $middleware);

        $callback();

        self::$prefix     = $previousPrefix;
        self::$middleware = $previousMiddleware;
    }

    private static function add(string $method, string $path, array|string $handler, string $name): void
    {
        $fullPath = self::$prefix . $path;
        $route = [
            'method'     => $method,
            'path'       => $fullPath,
            'handler'    => $handler,
            'middleware' => self::$middleware,
            'pattern'    => self::pathToPattern($fullPath),
        ];

        self::$routes[] = $route;

        if ($name) {
            self::$namedRoutes[$name] = $fullPath;
        }
    }

    // ── Resolução ─────────────────────────────────────────────────

    public static function dispatch(string $method, string $uri): void
    {
        // Suporte a _method em forms (PUT, DELETE via POST)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $uri = parse_url($uri, PHP_URL_PATH);

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) continue;

            $params = [];
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Executa middlewares
                foreach ($route['middleware'] as $mw) {
                    $middleware = new $mw();
                    $middleware->handle();
                }

                // Executa o handler
                self::callHandler($route['handler'], $params);
                return;
            }
        }

        // 404
        http_response_code(404);
        require VIEW_PATH . '/errors/404.php';
    }

    private static function callHandler(array|string $handler, array $params): void
    {
        if (is_string($handler)) {
            // 'ControllerClass@method'
            [$class, $method] = explode('@', $handler);
        } else {
            [$class, $method] = $handler;
        }

        $fullClass = str_contains($class, '\\') ? $class : "App\\Controllers\\{$class}";
        $controller = new $fullClass();
        $controller->$method($params);
    }

    private static function pathToPattern(string $path): string
    {
        // Converte /alunos/{id} em regex nomeado
        $pattern = preg_replace('/\{([a-z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    // ── URL helpers ───────────────────────────────────────────────

    public static function url(string $name, array $params = []): string
    {
        $path = self::$namedRoutes[$name] ?? $name;
        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', $value, $path);
        }
        return $path;
    }
}
