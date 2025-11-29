<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Todo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
final class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function save(Comment $comment): void
    {
        $em = $this->getEntityManager();
        $em->persist($comment);
        $em->flush();
    }

    /** @return Comment[] */
    public function listByTodo(Todo $todo): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.todo = :todo')
            ->setParameter('todo', $todo)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()->getResult();
    }
}
