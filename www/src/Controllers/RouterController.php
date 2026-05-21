<?php

namespace Smarty\Controllers;

use PDO;
use Smarty\Smarty as SmartyEngine;

class RouterController
{
    private SmartyEngine $smarty;

    public function __construct(public array $routes, public PDO $db)
    {
        $this->smarty = $this->createSmarty();
    }

    /**
     * @param array<string, string> $params
     */
    public function render(string $page, array $params = [], string $title = '', string $description = ''): void
    {
        $page = basename($page);
        $pageTemplate = 'pages/' . $page . '.tpl';

        if (!$this->smarty->templateExists($pageTemplate)) {
            $this->notFound($page, 'Template');
            return;
        }

        $this->smarty->assign('page', $page);
        $this->smarty->assign('title', $title);
        $this->smarty->assign('description', $description);
        foreach ($params as $name => $value) {
            $this->smarty->assign($name, $value);
        }
        foreach ($this->preparePageData($page, $params) as $name => $value) {
            $this->smarty->assign($name, $value);
        }

        header('Content-Type: text/html; charset=utf-8');
        $this->smarty->display('layout.tpl');
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
     * @return array{page: string, params: array<string, string>, title: string, description: string}|null
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
        $this->smarty->assign('path', $path);
        $this->smarty->assign('what', $what);
        $this->smarty->assign('title', '404');
        $this->smarty->assign('description', 'Not found');
        header('Content-Type: text/html; charset=utf-8');
        $this->smarty->display('404.tpl');
    }

    private function createSmarty(): SmartyEngine
    {
        $smarty = new SmartyEngine();
        $viewDir = __DIR__ . '/../View';
        $varDir = getenv('SMARTY_VAR_DIR') ?: dirname(__DIR__, 3) . '/var/smarty';

        $smarty->setTemplateDir($viewDir);
        $smarty->setCompileDir($varDir . '/compile');
        $smarty->setCacheDir($varDir . '/cache');

        return $smarty;
    }

    /**
     * @param array<string, string> $params
     * @return array<string, mixed>
     */
    private function preparePageData(string $page, array $params): array
    {
        return match ($page) {
            'article' => ['article' => $this->fetchArticle($params['id'] ?? '')],
            'category' => ['category' => $this->fetchCategory($params['id'] ?? '')],
            default => [],
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchArticle(string $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM articles WHERE id = ?');
        $stmt->execute([(int) $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchCategory(string $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([(int) $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }
}
