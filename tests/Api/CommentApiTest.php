<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\Comment;
use App\Entity\Todo;

final class CommentApiTest extends BaseApiTestCase
{
    private function createTodo(string $title = 'T1'): string
    {
        $this->client->jsonRequest('POST', '/todos', ['title' => $title]);
        self::assertResponseStatusCodeSame(201);

        return $this->json()['id'];
    }

    public function testListCommentsEmpty(): void
    {
        $id = $this->createTodo('No comments yet');
        $this->client->request('GET', "/todos/$id/comments");
        self::assertResponseIsSuccessful();
        $data = $this->json();
        self::assertIsArray($data);
        self::assertCount(0, $data);

        // Verify database has zero comments for this todo
        /** @var Todo|null $todo */
        $todo = $this->em()->getRepository(Todo::class)->find($id);
        self::assertNotNull($todo);
        /** @var array<int, Comment> $comments */
        $comments = $this->em()->getRepository(Comment::class)->findBy(['todo' => $todo]);
        self::assertCount(0, $comments);
    }

    public function testListCommentsUnknownTodo404(): void
    {
        $this->client->request('GET', '/todos/00000000-0000-0000-0000-000000000000/comments');
        self::assertResponseStatusCodeSame(404);
        $err = $this->json();
        self::assertSame('resource.not_found', $err['code']);
    }

    public function testCreateComment201(): void
    {
        $id = $this->createTodo('Task');
        $this->client->jsonRequest('POST', "/todos/$id/comments", ['message' => 'First!']);
        self::assertResponseStatusCodeSame(201);
        $c = $this->json();
        self::assertArrayHasKey('id', $c);
        self::assertSame($id, $c['todoId']);
        self::assertSame('First!', $c['message']);
        self::assertArrayHasKey('createdAt', $c);

        // Verify comment persisted in database
        /** @var Comment|null $db */
        $db = $this->em()->getRepository(Comment::class)->find($c['id']);
        self::assertNotNull($db);
        self::assertSame('First!', $db->getMessage());
        self::assertSame($id, $db->getTodoId());
    }

    public function testCreateCommentValidation422(): void
    {
        $id = $this->createTodo('Task');
        $this->client->jsonRequest('POST', "/todos/$id/comments", ['message' => '']);
        self::assertResponseStatusCodeSame(422);
        self::assertResponseHeaderSame('content-type', 'application/problem+json');
        $err = $this->json();
        self::assertSame('validation.failed', $err['code']);
        self::assertArrayHasKey('violations', $err);
    }

    public function testCreateCommentUnknownTodo404(): void
    {
        $this->client->jsonRequest('POST', '/todos/00000000-0000-0000-0000-000000000000/comments', ['message' => 'Hello']);
        self::assertResponseStatusCodeSame(404);
    }

    public function testCreateCommentOnDoneTodo409(): void
    {
        $id = $this->createTodo('To be done');
        // Mark as done
        $this->client->jsonRequest('PATCH', "/todos/$id", ['status' => 'done']);
        self::assertResponseIsSuccessful();

        // Try to add comment
        $this->client->jsonRequest('POST', "/todos/$id/comments", ['message' => 'Should fail']);
        self::assertResponseStatusCodeSame(409);
        $err = $this->json();
        self::assertSame('todo.comment.forbidden_on_done', $err['code']);
    }
}
