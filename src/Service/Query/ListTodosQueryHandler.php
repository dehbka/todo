<?php

declare(strict_types=1);

namespace App\Service\Query;

use App\Entity\Todo;
use App\Repository\TodoRepository;

final class ListTodosQueryHandler
{
    public function __construct(private readonly TodoRepository $todos)
    {
    }

    /** @return Todo[] */
    public function handle(ListTodosQuery $query): array
    {
        return $this->todos->all();
    }
}
