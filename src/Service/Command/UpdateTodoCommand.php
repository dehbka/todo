<?php

declare(strict_types=1);

namespace App\Service\Command;

final class UpdateTodoCommand
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $title = null,
        public readonly ?string $status = null,
    ) {
    }
}
