<?php

namespace Smarty\Controllers;

use PDO;

class RouterController
{
    public array $routes = [];

    public function __construct(array $routes, public PDO $db)
    {
        $this->routes = $routes;
        $this->db = $db;
    }

    /**
     * @param array<string, string> $params Variables passed into the template (from URL segments).
     */
    public function render(string $page, array $params = [], string $title = '', string $description = ''): void
    {
        $page = basename($page);
        $templateFile = __DIR__ . '/../View/Pages/' . $page . '.php';

        if (!is_file($templateFile)) {
            $this->notFound($page, 'Template');
            return;
        }

        header('Content-Type: text/html; charset=utf-8');
        extract($params, EXTR_SKIP);
        require __DIR__ . '/../View/templates/header.php';
        $db = $this->db;
        require $templateFile;
        require __DIR__ . '/../View/templates/footer.php';
    }

    public function dispatch(string $uri): void
    {
        $path = $this->normalizePath(parse_url($uri, PHP_URL_PATH) ?: '/');
        $match = $this->match($path);

        if ($match === null) {
            $this->notFound($path, 'Page');
            return;
        }

        $this->render($match['page'], $match['params'], $match['title'], $match['description']);
    }

    /**
     * @return array{page: string, params: array<string, string>}|null
     */
    private function match(string $path): ?array
    {
        foreach ($this->routes as $route) {
            $paramNames = [];
            $pattern = preg_replace_callback(
                '/\{(\w+)\}/',
                static function (array $matches) use (&$paramNames): string {
                    $paramNames[] = $matches[1];
                    return '([^/]+)';
                },
                $route['path'],
            );

            if (!preg_match('#^' . $pattern . '$#', $path, $found)) {
                continue;
            }

            array_shift($found);
            $params = [];
            foreach ($paramNames as $index => $name) {
                $params[$name] = $found[$index] ?? '';
            }

            return [
                'page' => $route['page'],
                'params' => $params,
                'title' => $route['title'],
                'description' => $route['description'],
            ];
        }

        return null;
    }

    private function normalizePath(string $path): string
    {
        $path = rawurldecode($path);

        if ($path === '/index.php') {
            return '/';
        }

        if (str_ends_with($path, '/index.php')) {
            $path = substr($path, 0, -strlen('/index.php')) ?: '/';
        }

        return rtrim($path, '/') ?: '/';
    }

    private function notFound(string $path, string $what): void
    {
        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><title>404</title></head><body>';
        echo '<h1>404 Not Found</h1>';
        echo '<p>' . $what . ' not found: ' . htmlspecialchars($path, ENT_QUOTES, 'UTF-8') . '</p>';
        echo '</body></html>';
    }
}
