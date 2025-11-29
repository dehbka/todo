<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'comments')]
class Comment
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Todo::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'todo_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Todo $todo;

    #[ORM\Column(type: 'string', length: 2000)]
    private string $message;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(Todo $todo, string $message)
    {
        $message = trim($message);
        if ('' === $message || mb_strlen($message) > 2000) {
            throw new \InvalidArgumentException('Message must be between 1 and 2000 characters.');
        }
        $this->id = Uuid::uuid4()->toString();
        $this->todo = $todo;
        $this->message = $message;
        $this->createdAt = new \DateTimeImmutable('now');
        $todo->addComment($this);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTodo(): Todo
    {
        return $this->todo;
    }

    public function getTodoId(): string
    {
        return $this->todo->getId();
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
