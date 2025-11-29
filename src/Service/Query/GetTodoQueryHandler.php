<?php

declare(strict_types=1);

namespace App\Service\Query;

use App\Entity\Todo;
use App\Repository\TodoRepository;
use App\Shared\NotFoundException;

final class GetTodoQueryHandler
{
    public function __construct(private readonly TodoRepository $todos)
    {
    }

    public function handle(GetTodoQuery $query): Todo
    {
        $todo = $this->todos->find($query->id);
        if (!$todo) {
            throw NotFoundException::resource('Todo not found');
        }

        return $todo;
    }
}
