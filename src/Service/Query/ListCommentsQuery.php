<?php

declare(strict_types=1);

namespace App\Service\Query;

final class ListCommentsQuery
{
    public function __construct(
        public readonly string $todoId,
    ) {
    }
}
