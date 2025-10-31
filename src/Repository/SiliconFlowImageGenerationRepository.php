<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowImageGeneration;

/**
 * @extends ServiceEntityRepository<SiliconFlowImageGeneration>
 */
#[Autoconfigure(public: true)]
class SiliconFlowImageGenerationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiliconFlowImageGeneration::class);
    }

    public function save(SiliconFlowImageGeneration $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SiliconFlowImageGeneration $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
