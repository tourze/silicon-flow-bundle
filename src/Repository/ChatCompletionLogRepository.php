<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\SiliconFlowBundle\Entity\ChatCompletionLog;

/**
 * @extends ServiceEntityRepository<ChatCompletionLog>
 */
#[Autoconfigure(public: true)]
final class ChatCompletionLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatCompletionLog::class);
    }

    /**
     * @return ChatCompletionLog[]
     */
    public function findRecent(int $limit = 20): array
    {
        /** @var array<ChatCompletionLog> */
        return $this->createQueryBuilder('log')
            ->orderBy('log.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(ChatCompletionLog $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ChatCompletionLog $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
