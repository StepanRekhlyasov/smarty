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

    public function list(): array
    {
        $stmt = DatabaseController::db()->prepare('SELECT * FROM categories');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
