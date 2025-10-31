<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowVideoGeneration;

/**
 * @extends ServiceEntityRepository<SiliconFlowVideoGeneration>
 */
#[Autoconfigure(public: true)]
class SiliconFlowVideoGenerationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiliconFlowVideoGeneration::class);
    }

    public function save(SiliconFlowVideoGeneration $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SiliconFlowVideoGeneration $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

