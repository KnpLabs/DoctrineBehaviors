<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\FilterableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\FilterableRepository;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/EntityManagerProvider.php';

final class FilterableRepositoryTest extends TestCase
{
    use EntityManagerProvider;

    public function testShouldFilterByNameUsingLike(): void
    {
        $this->createEntities();
        /** @var FilterableRepository $repository */
        $repository = $this->getEntityManager()->getRepository(FilterableEntity::class);

        $collection = $repository->filterBy(['e:name' => 'name'])->getQuery()->execute();

        $this->assertCount(2, $collection);
        $this->assertSame('name1', $collection[0]->getName());
        $this->assertSame('name2', $collection[1]->getName());
    }

    public function testShouldFilterByCodeUsingEqual(): void
    {
        $this->createEntities();

        $repository = $this->getEntityManager()->getRepository(FilterableEntity::class);

        $collection = $repository->filterBy(['e:code' => '2'])->getQuery()->execute();

        $this->assertCount(1, $collection);
        $this->assertSame('name1', $collection[0]->getName());
    }

    protected function getUsedEntityFixtures()
    {
        return [FilterableEntity::class];
    }

    private function createEntities(): void
    {
        $entityManager = $this->getEntityManager();

        foreach ([
            2 => 'name1',
            20 => 'name2',
            40 => 'otherValue',
        ] as $code => $name) {
            $entity = new FilterableEntity();
            $entity->setCode($code);
            $entity->setName($name);

            $entityManager->persist($entity);
        }
        $entityManager->flush();
    }
}
