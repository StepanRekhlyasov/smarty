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

    public function create(string $title, string $description): int
    {
        $stmt = DatabaseController::db()->prepare(
            'INSERT INTO categories (title, description) VALUES (?, ?)',
        );
        $stmt->execute([$title, $description]);

        return (int) DatabaseController::db()->lastInsertId();
    }

    /**
     * Articles that belong ONLY to this category (will be deleted with it).
     */
    public function countExclusiveArticles(int $categoryId): int
    {
        $stmt = DatabaseController::db()->prepare(
            'SELECT COUNT(*)
             FROM articles a
             INNER JOIN articles_categories ac ON ac.article_id = a.id
             WHERE ac.category_id = ?
               AND (SELECT COUNT(*) FROM articles_categories WHERE article_id = a.id) = 1',
        );
        $stmt->execute([$categoryId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Articles that belong to this category AND to at least one other category (will be kept).
     */
    public function countSharedArticles(int $categoryId): int
    {
        $stmt = DatabaseController::db()->prepare(
            'SELECT COUNT(*)
             FROM articles a
             INNER JOIN articles_categories ac ON ac.article_id = a.id
             WHERE ac.category_id = ?
               AND (SELECT COUNT(*) FROM articles_categories WHERE article_id = a.id) > 1',
        );
        $stmt->execute([$categoryId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Deletes articles belonging exclusively to this category, then deletes the category.
     */
    public function delete(int $id): bool
    {
        $db = DatabaseController::db();

        // Собираем ID статей, которые принадлежат только этой категории
        $stmt = $db->prepare(
            'SELECT a.id FROM articles a
             INNER JOIN articles_categories ac ON ac.article_id = a.id
             WHERE ac.category_id = ?
               AND (SELECT COUNT(*) FROM articles_categories ac2 WHERE ac2.article_id = a.id) = 1',
        );
        $stmt->execute([$id]);
        $exclusiveIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        // Удаляем их связи из pivot-таблицы (FK article_id без CASCADE)
        if (!empty($exclusiveIds)) {
            $placeholders = implode(',', array_fill(0, count($exclusiveIds), '?'));
            $stmt = $db->prepare("DELETE FROM articles_categories WHERE article_id IN ({$placeholders})");
            $stmt->execute($exclusiveIds);

            $stmt = $db->prepare("DELETE FROM articles WHERE id IN ({$placeholders})");
            $stmt->execute($exclusiveIds);
        }

        // Удаляем оставшиеся связи pivot (общие статьи просто отвязываются)
        $stmt = $db->prepare('DELETE FROM articles_categories WHERE category_id = ?');
        $stmt->execute([$id]);

        // Удаляем саму категорию
        $stmt = $db->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }
}
