<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Todo;
use App\Http\Dto\CommentDto;
use App\Http\Dto\TodoDto;
use App\Http\Request\CreateCommentRequest;
use App\Http\Request\CreateTodoRequest;
use App\Http\Request\UpdateTodoRequest;
use App\Service\Command\CreateCommentCommand;
use App\Service\Command\CreateCommentCommandHandler;
use App\Service\Command\CreateTodoCommand;
use App\Service\Command\CreateTodoCommandHandler;
use App\Service\Command\UpdateTodoCommand;
use App\Service\Command\UpdateTodoCommandHandler;
use App\Service\Query\GetTodoQuery;
use App\Service\Query\GetTodoQueryHandler;
use App\Service\Query\ListCommentsQuery;
use App\Service\Query\ListCommentsQueryHandler;
use App\Service\Query\ListTodosQuery;
use App\Service\Query\ListTodosQueryHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class TodoController extends AbstractController
{
    public function __construct(
        private readonly CreateTodoCommandHandler $createTodo,
        private readonly ListTodosQueryHandler $listTodos,
        private readonly GetTodoQueryHandler $getTodo,
        private readonly UpdateTodoCommandHandler $updateTodo,
        private readonly CreateCommentCommandHandler $createComment,
        private readonly ListCommentsQueryHandler $listComments,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route(path: '/todos', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateTodoRequest $dto): JsonResponse
    {
        $violations = $this->validator->validate($dto);
        if (\count($violations) > 0) {
            throw new ValidationFailedException($dto, $violations);
        }
        $todo = $this->createTodo->handle(new CreateTodoCommand($dto->title));

        return $this->json(TodoDto::fromEntity($todo)->toArray(), 201);
    }

    #[Route(path: '/todos', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $todos = $this->listTodos->handle(new ListTodosQuery());

        return $this->json(array_map(fn (Todo $t) => TodoDto::fromEntity($t)->toArray(), $todos));
    }

    #[Route(path: '/todos/{id}', methods: ['GET'])]
    public function getOne(string $id): JsonResponse
    {
        $todo = $this->getTodo->handle(new GetTodoQuery($id));

        return $this->json(TodoDto::fromEntity($todo)->toArray());
    }

    #[Route(path: '/todos/{id}', methods: ['PATCH'])]
    public function update(string $id, #[MapRequestPayload] UpdateTodoRequest $dto): JsonResponse
    {
        $violations = $this->validator->validate($dto);
        if (\count($violations) > 0) {
            throw new ValidationFailedException($dto, $violations);
        }
        $todo = $this->updateTodo->handle(new UpdateTodoCommand($id, $dto->title, $dto->status));

        return $this->json(TodoDto::fromEntity($todo)->toArray());
    }

    #[Route(path: '/todos/{id}/comments', methods: ['POST'])]
    public function addComment(string $id, #[MapRequestPayload] CreateCommentRequest $dto): JsonResponse
    {
        $violations = $this->validator->validate($dto);
        if (\count($violations) > 0) {
            throw new ValidationFailedException($dto, $violations);
        }
        $comment = $this->createComment->handle(new CreateCommentCommand($id, $dto->message));

        return $this->json(CommentDto::fromEntity($comment)->toArray(), 201);
    }

    #[Route(path: '/todos/{id}/comments', methods: ['GET'])]
    public function listComments(string $id): JsonResponse
    {
        $comments = $this->listComments->handle(new ListCommentsQuery($id));

        return $this->json(array_map(fn (Comment $c) => CommentDto::fromEntity($c)->toArray(), $comments));
    }


}
