<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;

/**
 * @extends ServiceEntityRepository<SiliconFlowConfig>
 */
#[Autoconfigure(public: true)]
class SiliconFlowConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiliconFlowConfig::class);
    }

    public function save(SiliconFlowConfig $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SiliconFlowConfig $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findActiveConfig(): ?SiliconFlowConfig
    {
        /** @var SiliconFlowConfig|null */
        return $this->createQueryBuilder('config')
            ->where('config.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('config.priority', 'DESC')
            ->addOrderBy('config.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return SiliconFlowConfig[]
     */
    public function findActiveConfigs(int $limit = 50): array
    {
        /** @var array<SiliconFlowConfig> */
        return $this->createQueryBuilder('config')
            ->where('config.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('config.priority', 'DESC')
            ->addOrderBy('config.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
