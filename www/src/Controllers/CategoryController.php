<?php

namespace Smarty\Controllers;

use PDO;
use Smarty\Models\Category;

final class CategoryController
{
    public function findById(int $id): ? Category
    {
        $stmt = DatabaseController::db()->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? Category::fromArray($row) : null;
    }

    /**
     * @return list<Category>
     */
    public function list(): array
    {
        $stmt = DatabaseController::db()->prepare('SELECT * FROM categories ORDER BY id');
        $stmt->execute();

        return array_map(fn (array $category) => Category::fromArray($category), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Only categories that have at least one article.
     *
     * @return list<Category>
     */
    public function listWithArticles(): array
    {
        $stmt = DatabaseController::db()->prepare(
            'SELECT DISTINCT c.*
             FROM categories c
             INNER JOIN articles_categories ac ON ac.category_id = c.id
             ORDER BY c.id',
        );
        $stmt->execute();

        return array_map(fn (array $category) => Category::fromArray($category), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @return list<Category>
     */
    public function findCategoriesByArticleId(int $articleId): array
    {
        $stmt = DatabaseController::db()->prepare(
            'SELECT c.*
             FROM categories c
             INNER JOIN articles_categories ac ON ac.category_id = c.id
             WHERE ac.article_id = ?
             ORDER BY c.id',
        );
        $stmt->execute([$articleId]);

        return array_map(fn (array $category) => Category::fromArray($category), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
