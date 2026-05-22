<?php

namespace Smarty\Controllers;

use PDO;
use Smarty\Models\Article;

final class ArticleController
{
    public function findById(int $id): ? Article
    {
        $stmt = DatabaseController::db()->prepare('SELECT * FROM articles WHERE id = ?');
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? Article::fromArray($row) : null;
    }

    public function incrementViews(int $id): void
    {
        $stmt = DatabaseController::db()->prepare(
            'UPDATE articles SET views_count = views_count + 1 WHERE id = ?',
        );
        $stmt->execute([$id]);
    }

    /**
     * @return list<Article>
     */
    public function list(): array
    {
        $stmt = DatabaseController::db()->prepare('SELECT * FROM articles ORDER BY created_at DESC');
        $stmt->execute();

        return array_map(fn (array $article) => Article::fromArray($article), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @return list<Article>
     */
    public function findLatestByCategory(int $categoryId, int $limit = 3): array
    {
        $stmt = DatabaseController::db()->prepare(
            'SELECT a.*
             FROM articles a
             INNER JOIN articles_categories ac ON ac.article_id = a.id
             WHERE ac.category_id = ?
             ORDER BY a.created_at DESC
             LIMIT ?',
        );
        $stmt->bindValue(1, $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(fn (array $article) => Article::fromArray($article), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @return list<Article>    
     */
    public function findByCategoryPaginated(int $categoryId, int $page, int $perPage, string $sort): array
    {
        $orderBy = $sort === 'views' ? 'a.views_count DESC' : 'a.created_at DESC';
        $offset = ($page - 1) * $perPage;

        $stmt = DatabaseController::db()->prepare(
            "SELECT a.*
             FROM articles a
             INNER JOIN articles_categories ac ON ac.article_id = a.id
             WHERE ac.category_id = ?
             ORDER BY {$orderBy}
             LIMIT ? OFFSET ?",
        );
        $stmt->bindValue(1, $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(2, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();

        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn (array $article) => Article::fromArray($article), $articles);
    }

    public function countByCategory(int $categoryId): int
    {
        $stmt = DatabaseController::db()->prepare(
            'SELECT COUNT(DISTINCT a.id)
             FROM articles a
             INNER JOIN articles_categories ac ON ac.article_id = a.id
             WHERE ac.category_id = ?',
        );
        $stmt->execute([$categoryId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return list<Article>
     */
    public function findSimilar(int $articleId, int $limit = 3): array
    {
        $stmt = DatabaseController::db()->prepare(
            'SELECT DISTINCT a.*
             FROM articles a
             INNER JOIN articles_categories ac ON ac.article_id = a.id
             WHERE ac.category_id IN (
                 SELECT category_id FROM articles_categories WHERE article_id = ?
             )
             AND a.id != ?
             ORDER BY RAND()
             LIMIT ?',
        );
        $stmt->bindValue(1, $articleId, PDO::PARAM_INT);
        $stmt->bindValue(2, $articleId, PDO::PARAM_INT);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(fn (array $article) => Article::fromArray($article), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @return list<Article>
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

        return array_map(fn (array $article) => Article::fromArray($article), $stmt->fetchAll(PDO::FETCH_ASSOC));
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

    public function create(string $title, string $content, string $description, ?string $imageUrl = null): int
    {
        $stmt = DatabaseController::db()->prepare(
            'INSERT INTO articles (title, content, description, image_url) VALUES (?, ?, ?, ?)',
        );
        $stmt->execute([$title, $content, $description, $imageUrl]);

        return (int) DatabaseController::db()->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $db = DatabaseController::db();

        $stmt = $db->prepare('DELETE FROM articles_categories WHERE article_id = ?');
        $stmt->execute([$id]);

        $stmt = $db->prepare('DELETE FROM articles WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }
}
