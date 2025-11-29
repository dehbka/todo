<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\Todo;

final class TodoApiTest extends BaseApiTestCase
{
    public function testListTodosInitiallyEmpty(): void
    {
        $this->client->request('GET', '/todos');
        self::assertResponseIsSuccessful();
        $data = $this->json();
        self::assertIsArray($data);
        self::assertCount(0, $data);

        // Also verify database is empty
        $repo = $this->em()->getRepository(Todo::class);
        /** @var array<int, Todo> $all */
        $all = $repo->findAll();
        self::assertCount(0, $all);
    }

    public function testCreateTodoAndFetch(): void
    {
        $this->client->jsonRequest('POST', '/todos', ['title' => 'Buy milk']);
        self::assertResponseStatusCodeSame(201);
        $todo = $this->json();
        self::assertArrayHasKey('id', $todo);
        self::assertSame('Buy milk', $todo['title']);
        self::assertSame('open', $todo['status']);
        self::assertArrayHasKey('createdAt', $todo);
        self::assertArrayHasKey('updatedAt', $todo);

        $id = $todo['id'];

        // Verify database persisted entity matches payload
        /** @var Todo|null $db */
        $db = $this->em()->getRepository(Todo::class)->find($id);
        self::assertNotNull($db);
        self::assertSame('Buy milk', $db->getTitle());
        self::assertSame('open', $db->getStatus());

        $this->client->request('GET', "/todos/$id");
        self::assertResponseIsSuccessful();
        $fetched = $this->json();
        self::assertSame($id, $fetched['id']);
        self::assertSame('Buy milk', $fetched['title']);
    }

    public function testCreateTodoValidation422(): void
    {
        $this->client->jsonRequest('POST', '/todos', ['title' => '']);
        self::assertResponseStatusCodeSame(422);
        self::assertResponseHeaderSame('content-type', 'application/problem+json');
        $err = $this->json();
        self::assertSame('validation.failed', $err['code']);
        self::assertSame('Validation failed', $err['message']);
        self::assertArrayHasKey('violations', $err);
        self::assertIsArray($err['violations']);
    }

    public function testGetUnknownTodoReturns404(): void
    {
        $this->client->request('GET', '/todos/00000000-0000-0000-0000-000000000000');
        self::assertResponseStatusCodeSame(404);
        $err = $this->json();
        self::assertSame('resource.not_found', $err['code']);
    }

    public function testUpdateTodoTitleAndStatus(): void
    {
        // Create
        $this->client->jsonRequest('POST', '/todos', ['title' => 'Task']);
        $todo = $this->json();
        $id = $todo['id'];

        // Update
        $this->client->jsonRequest('PATCH', "/todos/$id", ['title' => 'Task updated', 'status' => 'done']);
        self::assertResponseIsSuccessful();
        $updated = $this->json();
        self::assertSame('Task updated', $updated['title']);
        self::assertSame('done', $updated['status']);

        // Verify changes persisted in database
        /** @var Todo|null $db */
        $db = $this->em()->getRepository(Todo::class)->find($id);
        self::assertNotNull($db);
        self::assertSame('Task updated', $db->getTitle());
        self::assertSame('done', $db->getStatus());
    }

    public function testUpdateUnknownTodoReturns404(): void
    {
        $this->client->jsonRequest('PATCH', '/todos/00000000-0000-0000-0000-000000000000', ['title' => 'X']);
        self::assertResponseStatusCodeSame(404);
        $err = $this->json();
        self::assertSame('resource.not_found', $err['code']);
    }

    public function testUpdateValidation422(): void
    {
        // Create
        $this->client->jsonRequest('POST', '/todos', ['title' => 'Task']);
        $id = $this->json()['id'];

        // Invalid title
        $this->client->jsonRequest('PATCH', "/todos/$id", ['title' => '']);
        self::assertResponseStatusCodeSame(422);
        self::assertResponseHeaderSame('content-type', 'application/problem+json');
        $err = $this->json();
        self::assertSame('validation.failed', $err['code']);

        // Invalid status
        $this->client->jsonRequest('PATCH', "/todos/$id", ['status' => 'invalid']);
        self::assertResponseStatusCodeSame(422);
    }
}
