<?php

namespace Smarty\Controllers;

use PDO;
use Smarty\Models\Article;

final class ArticleController
{
    public function findById(int $id): ?array
    {
        $stmt = DatabaseController::db()->prepare('SELECT * FROM articles WHERE id = ?');
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    /**
     * @return list<array<string, mixed>>
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

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findArticlesByCategoryId(int $categoryId): array
    {
        $stmt = DatabaseController::db()->prepare(
            'SELECT a.*
             FROM articles a
             INNER JOIN articles_categories ac ON ac.article_id = a.id
             WHERE ac.category_id = ?
             ORDER BY a.id',
        );
        $stmt->execute([$categoryId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function attachCategory(int $articleId, int $categoryId): void
    {
        $stmt = DatabaseController::db()->prepare(
            'INSERT IGNORE INTO articles_categories (article_id, category_id) VALUES (?, ?)',
        );
        $stmt->execute([$articleId, $categoryId]);
    }

    public function detachCategory(int $articleId, int $categoryId): void
    {
        $stmt = DatabaseController::db()->prepare(
            'DELETE FROM articles_categories WHERE article_id = ? AND category_id = ?',
        );
        $stmt->execute([$articleId, $categoryId]);
    }

    public function syncCategories(int $articleId, array $categoryIds): void
    {
        $stmt = DatabaseController::db()->prepare('DELETE FROM articles_categories WHERE article_id = ?');
        $stmt->execute([$articleId]);

        foreach ($categoryIds as $categoryId) {
            $this->attachCategory($articleId, (int) $categoryId);
        }
    }

    public function list(): array
    {
        $stmt = DatabaseController::db()->prepare('SELECT * FROM articles');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
