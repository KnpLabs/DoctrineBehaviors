<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\FilterableEntity;

final class FilterableRepository
{
    /**
     * @var ObjectRepository|EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(FilterableEntity::class);
    }

    /**
     * @param string[] $filters E.g. ['e:name' => 'nameValue'] where "e" is entity alias query, so we can filter using joins.
     */
    public function filterBy(array $filters, ?QueryBuilder $queryBuilder = null): QueryBuilder
    {
        if ($queryBuilder === null) {
            $queryBuilder = $this->repository->createQueryBuilder('e');
        }

        foreach ($filters as $col => $value) {
            foreach ($this->getColumnParameters($col) as $colName => $colParam) {
                if (in_array($col, $this->getLikeFilterColumns(), true)) {
                    $queryBuilder->andWhere(sprintf('%s LIKE :%s', $colName, $colParam))
                        ->setParameter($colParam, '%' . $value . '%');
                }

                if (in_array($col, $this->getILikeFilterColumns(), true)) {
                    $queryBuilder->andWhere(sprintf('LOWER(%s) LIKE :%s', $colName, $colParam))
                        ->setParameter($colParam, '%' . strtolower($value) . '%');
                }

                if (in_array($col, $this->getEqualFilterColumns(), true)) {
                    $queryBuilder->andWhere(sprintf('%s = :%s', $colName, $colParam))
                        ->setParameter($colParam, $value);
                }

                if (in_array($col, $this->getInFilterColumns(), true)) {
                    $queryBuilder->andWhere($queryBuilder->expr()->in(sprintf('%s', $colName), (array) $value));
                }
            }
        }

        return $queryBuilder;
    }

    public function getILikeFilterColumns()
    {
        return [];
    }

    public function getLikeFilterColumns()
    {
        return ['e:name'];
    }

    public function getEqualFilterColumns()
    {
        return ['e:code'];
    }

    public function getInFilterColumns()
    {
        return [];
    }

    /**
     * @return string[]
     */
    private function getColumnParameters(string $col): array
    {
        $colName = (string) str_replace(':', '.', $col);
        $colParam = (string) str_replace(':', '_', $col);

        return [$colName => $colParam];
    }
}
