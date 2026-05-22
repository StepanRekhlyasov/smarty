<?php

namespace Smarty\Models;

final class Category
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly ?int $id = null,
        public readonly ?string $createdAt = null,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            title: (string) $row['title'],
            description: (string) $row['description'],
            id: (int) $row['id'],
            createdAt: isset($row['created_at']) ? (string) $row['created_at'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'created_at' => $this->createdAt,
        ];
    }
}
