<?php

declare(strict_types=1);

namespace App\Service\Command;

final class CreateTodoCommand
{
    public function __construct(
        public readonly string $title,
    ) {
    }
}
