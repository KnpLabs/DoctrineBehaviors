<?php

namespace Knp\DoctrineBehaviors\Repository;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Class SluggableRepository
 */
class SluggableRepository extends EntityRepository
{
    /**
     * @param EntityManager $em
     * @param ClassMetadata $class
     */
    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
    }

    /**
     * @param $entity
     * @param $uniqueSlug
     * @return int
     */
    public function isSlugUniqueFor($entity, $uniqueSlug)
    {
        $qb = $this->createQueryBuilder('e');
        $qb
            ->select('COUNT(e)')
            ->andWhere('e.id != :id')
            ->andWhere('e.slug = :slug')
            ->setParameter('id', $entity->getId())
            ->setParameter('slug', $uniqueSlug)
        ;

        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}
