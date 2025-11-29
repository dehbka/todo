<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\TodoRepository;
use App\Shared\BusinessRuleViolation;
use App\Shared\NotFoundException;

final class CreateCommentCommandHandler
{
    public function __construct(
        private readonly TodoRepository $todos,
        private readonly CommentRepository $comments,
    ) {
    }

    public function handle(CreateCommentCommand $command): Comment
    {
        $todo = $this->todos->find($command->todoId);
        if (!$todo) {
            throw NotFoundException::resource('Todo not found');
        }
        if (!$todo->canAcceptComments()) {
            throw BusinessRuleViolation::conflict(code: 'todo.comment.forbidden_on_done', message: 'Cannot add a comment to a completed Todo.');
        }
        $comment = new Comment($todo, $command->message);
        $this->comments->save($comment);

        return $comment;
    }
}
