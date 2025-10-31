<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowModel;

/**
 * @extends ServiceEntityRepository<SiliconFlowModel>
 */
#[Autoconfigure(public: true)]
class SiliconFlowModelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiliconFlowModel::class);
    }

    public function save(SiliconFlowModel $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SiliconFlowModel $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param array<string> $modelIds
     */
    public function deactivateAllExcept(array $modelIds): void
    {
        $qb = $this->createQueryBuilder('model')
            ->update()
            ->set('model.isActive', ':inactive')
            ->setParameter('inactive', false);

        if ([] !== $modelIds) {
            $qb->andWhere('model.modelId NOT IN (:ids)')
                ->setParameter('ids', $modelIds);
        }

        $qb->getQuery()->execute();
    }

    /**
     * @return array<SiliconFlowModel>
     */
    public function findActiveModels(): array
    {
        /** @var array<SiliconFlowModel> */
        return $this->createQueryBuilder('model')
            ->where('model.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('model.modelId', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
