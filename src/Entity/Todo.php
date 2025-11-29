<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\TodoStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'todos')]
class Todo
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private string $id;

    #[ORM\Column(type: 'string', length: 200)]
    private string $title;

    #[ORM\Column(enumType: TodoStatus::class)]
    private TodoStatus $status = TodoStatus::OPEN;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, Comment> */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'todo', cascade: ['persist'], orphanRemoval: true)]
    private Collection $comments;

    public function __construct(string $title)
    {
        $title = trim($title);
        if ('' === $title || mb_strlen($title) > 200) {
            throw new \InvalidArgumentException('Title must be between 1 and 200 characters.');
        }
        $this->id = Uuid::uuid4()->toString();
        $this->title = $title;
        $this->status = TodoStatus::OPEN;
        $now = new \DateTimeImmutable('now');
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->comments = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getStatus(): string
    {
        return $this->status->value;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /** @return Comment[] */
    public function getComments(): array
    {
        return $this->comments->toArray();
    }

    public function rename(?string $title): void
    {
        if (null === $title) {
            return;
        }
        $title = trim($title);
        if ('' === $title || mb_strlen($title) > 200) {
            throw new \InvalidArgumentException('Title must be between 1 and 200 characters.');
        }
        $this->title = $title;
        $this->touch();
    }

    public function changeStatus(?string $status): void
    {
        if (null === $status) {
            return;
        }
        $new = TodoStatus::tryFrom($status);
        if (null === $new) {
            throw new \InvalidArgumentException('Invalid status.');
        }
        $this->status = $new;
        $this->touch();
    }

    public function canAcceptComments(): bool
    {
        return TodoStatus::DONE !== $this->status;
    }

    public function addComment(Comment $comment): void
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
        }
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable('now');
    }
}
