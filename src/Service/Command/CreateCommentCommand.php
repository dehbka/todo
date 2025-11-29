<?php

declare(strict_types=1);

namespace App\Service\Command;

final class CreateCommentCommand
{
    public function __construct(
        public readonly string $todoId,
        public readonly string $message,
    ) {
    }
}
