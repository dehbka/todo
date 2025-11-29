<?php

declare(strict_types=1);

namespace App\Http\Dto;

use App\Entity\Comment;

final class CommentDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $todoId,
        public readonly string $message,
        public readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public static function fromEntity(Comment $comment): self
    {
        return new self(
            $comment->getId(),
            $comment->getTodoId(),
            $comment->getMessage(),
            $comment->getCreatedAt(),
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'todoId' => $this->todoId,
            'message' => $this->message,
            'createdAt' => $this->createdAt->format(\DATE_ATOM),
        ];
    }
}
