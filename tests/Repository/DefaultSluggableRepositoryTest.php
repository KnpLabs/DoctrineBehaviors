<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Repository\DefaultSluggableRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DefaultSluggableRepositoryTest extends TestCase
{
    /**
     * @var EntityManagerInterface&MockObject
     */
    private $entityManager;

    private DefaultSluggableRepository $defaultSluggableRepository;

    protected function setUp(): void
    {
        $this->defaultSluggableRepository = new DefaultSluggableRepository(
            $this->entityManager = $this->createMock(EntityManagerInterface::class)
        );
    }

    public function testIsSlugUniqueFor(): void
    {
        $sluggable = $this->createMock(SluggableInterface::class);
        $entityClass = $sluggable::class;
        $uniqueSlug = 'foobar';

        $this->entityManager->expects(self::once())
            ->method('getClassMetadata')
            ->with($entityClass)
            ->willReturn($metadata = $this->createMock(ClassMetadata::class));

        $metadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($sluggable)
            ->willReturn([
                'id' => null,
                'slug' => 'foo',
                'id.id' => '123',
            ]);

        $this->entityManager->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder = $this->createMock(QueryBuilder::class));

        $queryBuilder->expects(self::once())
            ->method('select')
            ->with('COUNT(e)')
            ->willReturnSelf();

        $queryBuilder->expects(self::once())
            ->method('from')
            ->with($entityClass, 'e')
            ->willReturnSelf();

        $queryBuilder->expects(self::exactly(2))
            ->method('andWhere')
            ->withConsecutive(['e.slug = :slug'], ['e.id.id != :id_id'])
            ->willReturnSelf();

        $queryBuilder->expects(self::exactly(2))
            ->method('setParameter')
            ->withConsecutive(['slug', $uniqueSlug], ['id_id', '123'])
            ->willReturnSelf();

        $queryBuilder->expects(self::once())
            ->method('getQuery')
            ->willReturn($query = $this->createMock(AbstractQuery::class));

        $query->expects(self::once())
            ->method('getSingleScalarResult')
            ->willReturn(1);

        self::assertFalse($this->defaultSluggableRepository->isSlugUniqueFor($sluggable, $uniqueSlug));
    }
}
