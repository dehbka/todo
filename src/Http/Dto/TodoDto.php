<?php

declare(strict_types=1);

namespace App\Http\Dto;

use App\Entity\Todo;

final class TodoDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $status,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function fromEntity(Todo $todo): self
    {
        return new self(
            $todo->getId(),
            $todo->getTitle(),
            $todo->getStatus(),
            $todo->getCreatedAt(),
            $todo->getUpdatedAt(),
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'createdAt' => $this->createdAt->format(\DATE_ATOM),
            'updatedAt' => $this->updatedAt->format(\DATE_ATOM),
        ];
    }
}
