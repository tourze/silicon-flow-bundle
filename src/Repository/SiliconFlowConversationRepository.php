<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConversation;

/**
 * @extends ServiceEntityRepository<SiliconFlowConversation>
 */
#[Autoconfigure(public: true)]
class SiliconFlowConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiliconFlowConversation::class);
    }

    public function save(SiliconFlowConversation $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SiliconFlowConversation $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return SiliconFlowConversation[]
     */
    public function findByContext(string $contextId, int $limit = 50): array
    {
        /** @var array<SiliconFlowConversation> */
        return $this->createQueryBuilder('conversation')
            ->where('conversation.contextId = :contextId')
            ->setParameter('contextId', $contextId)
            ->orderBy('conversation.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
