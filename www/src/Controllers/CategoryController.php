<?php

namespace Smarty\Controllers;

use PDO;

final class CategoryController
{
    public function findById(int $id): ?array
    {
        $stmt = DatabaseController::db()->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(): array
    {
        $stmt = DatabaseController::db()->prepare('SELECT * FROM categories ORDER BY id');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Only categories that have at least one article.
     *
     * @return list<array<string, mixed>>
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

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
