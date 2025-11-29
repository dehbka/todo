<?php

declare(strict_types=1);

namespace App\Service\Query;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\TodoRepository;
use App\Shared\NotFoundException;

final class ListCommentsQueryHandler
{
    public function __construct(
        private readonly TodoRepository $todos,
        private readonly CommentRepository $comments,
    ) {
    }

    /** @return Comment[] */
    public function handle(ListCommentsQuery $query): array
    {
        $todo = $this->todos->find($query->todoId);
        if (!$todo) {
            throw NotFoundException::resource('Todo not found');
        }

        return $this->comments->listByTodo($todo);
    }
}
