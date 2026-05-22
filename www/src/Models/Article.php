<?php

namespace Smarty\Models;

final class Article
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly string $description,
        public readonly ?int $id = null,
        public readonly int $viewsCount = 0,
        public readonly ?string $imageUrl = null,
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
            content: (string) $row['content'],
            description: (string) $row['description'],
            id: (int) $row['id'],
            viewsCount: (int) $row['views_count'],
            imageUrl: isset($row['image_url']) ? (string) $row['image_url'] : null,
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
            'content' => $this->content,
            'description' => $this->description,
            'views_count' => $this->viewsCount,
            'image_url' => $this->imageUrl,
            'created_at' => $this->createdAt,
        ];
    }

    public function getImage(string $type): string
    {
        if (is_string($this->imageUrl) && strpos($this->imageUrl, 'https://picsum.photos/') === 0) {
            return match ($type) {
                'preview' => $this->imageUrl . '/140/100',
                'thumbnail' => $this->imageUrl . '/360/200',
                'original' => $this->imageUrl . '/1200/500',
            };
        }

        return $this->imageUrl ?? '';
    }
}
