<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\Todo;
use App\Repository\TodoRepository;

final class CreateTodoCommandHandler
{
    public function __construct(private readonly TodoRepository $todos)
    {
    }

    public function handle(CreateTodoCommand $command): Todo
    {
        $todo = new Todo($command->title);
        $this->todos->save($todo);

        return $todo;
    }
}
