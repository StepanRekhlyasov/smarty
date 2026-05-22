<?php

namespace Smarty\Controllers;

use Smarty\Smarty as SmartyEngine;
use Smarty\Models\Article;

class RouterController
{
    private SmartyEngine $smarty;

    private ArticleController $articleController;

    private CategoryController $categoryController;

    public function __construct(public array $routes)
    {
        $this->smarty = $this->createSmarty();
        $this->articleController = new ArticleController();
        $this->categoryController = new CategoryController();
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
            'home' => $this->homeData(),
            'article' => $this->articleData((int) ($params['id'] ?? 0)),
            'category' => $this->categoryData((int) ($params['id'] ?? 0)),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function homeData(): array
    {
        $categories = $this->categoryController->listWithArticles();
        $sections = [];

        foreach ($categories as $category) {
            $articles = $this->articleController->findLatestByCategory((int) $category->id, 3);
            if (!empty($articles)) {
                $sections[] = ['category' => $category, 'articles' => $articles];
            }
        }

        return [
            'sections'      => $sections,
            'allCategories' => $this->categoryController->list(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function articleData(int $id): array
    {

        $this->articleController->incrementViews($id);
        $article = $this->articleController->findById($id);

        if ($article === null) {
            return ['article' => null];
        }

        $articleCategories = $this->categoryController->findCategoriesByArticleId($id);

        return [
            'article'            => $article,
            'articleCategories'  => $articleCategories,
            'articleCategoryIds' => array_map(fn ($c) => $c->id, $articleCategories),
            'allCategories'      => $this->categoryController->list(),
            'similar'            => $this->articleController->findSimilar($id, 3),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryData(int $id): array
    {
        $category = $this->categoryController->findById($id);

        if ($category === null) {
            return ['category' => null];
        }

        $allowedSorts = ['date', 'views'];
        $sort = in_array($_GET['sort'] ?? '', $allowedSorts, true) ? $_GET['sort'] : 'date';
        $perPage = 6;
        $totalCount = $this->articleController->countByCategory($id);
        $totalPages = max(1, (int) ceil($totalCount / $perPage));
        $currentPage = max(1, min($totalPages, (int) ($_GET['page'] ?? 1)));

        return [
            'category'       => $category,
            'articles'       => $this->articleController->findByCategoryPaginated($id, $currentPage, $perPage, $sort),
            'sort'           => $sort,
            'currentPage'    => $currentPage,
            'totalPages'     => $totalPages,
            'totalCount'     => $totalCount,
            'perPage'        => $perPage,
            'exclusiveCount' => $this->categoryController->countExclusiveArticles($id),
            'sharedCount'    => $this->categoryController->countSharedArticles($id),
        ];
    }
}
