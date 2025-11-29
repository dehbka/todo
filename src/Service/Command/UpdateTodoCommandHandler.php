<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Repository\TodoRepository;
use App\Shared\NotFoundException;

final class UpdateTodoCommandHandler
{
    public function __construct(private readonly TodoRepository $todos)
    {
    }

    public function handle(UpdateTodoCommand $command)
    {
        $todo = $this->todos->find($command->id);
        if (!$todo) {
            throw NotFoundException::resource('Todo not found');
        }
        $todo->rename($command->title);
        $todo->changeStatus($command->status);
        $this->todos->save($todo);

        return $todo;
    }
}
