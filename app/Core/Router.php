<?php
// app/core/Router.php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void  { $this->add('GET',  $path, $handler); }
    public function post(string $path, string $handler): void { $this->add('POST', $path, $handler); }
    public function any(string $path, string $handler): void  { $this->add('ANY',  $path, $handler); }

    private function add(string $method, string $path, string $handler): void
    {
        $this->routes[] = [
            'method'  => $method,
            'pattern' => $this->toRegex($path),
            'handler' => $handler,
        ];
    }

    private function toRegex(string $path): string
    {
        $p = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $p . '/?$#';
    }

    public function dispatch(string $uri, string $method): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== 'ANY' && $route['method'] !== $method) continue;
            if (!preg_match($route['pattern'], $uri, $matches)) continue;

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            [$class, $action] = explode('@', $route['handler']);

            if (!class_exists($class)) {
                $this->abort(500, "Controller not found: {$class}");
                return;
            }
            $ctrl = new $class();
            if (!method_exists($ctrl, $action)) {
                $this->abort(500, "Method not found: {$action}");
                return;
            }
            call_user_func_array([$ctrl, $action], $params);
            return;
        }
        $this->abort(404, 'Page not found');
    }

    private function abort(int $code, string $msg): void
    {
        http_response_code($code);
        // Try each panel's error view
        $candidates = [
            ADMIN_VIEWS  . "/errors/{$code}.php",
            FRONTEND_VIEWS . "/errors/{$code}.php",
            SELLER_VIEWS . "/errors/{$code}.php",
        ];
        foreach ($candidates as $f) {
            if (file_exists($f)) { include $f; exit; }
        }
        echo "<h1>{$code} — {$msg}</h1>";
        exit;
    }
}
