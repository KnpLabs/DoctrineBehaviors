<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\FilterableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\FilterableRepository;

final class FilterableRepositoryTest extends AbstractBehaviorTestCase
{
    public function testShouldFilterByNameUsingLike(): void
    {
        $this->createEntities();

        /** @var FilterableRepository $repository */
        $repository = $this->entityManager->getRepository(FilterableEntity::class);

        $collection = $repository->filterBy(['e:name' => 'name'])->getQuery()->execute();

        $this->assertCount(2, $collection);
        $this->assertSame('name1', $collection[0]->getName());
        $this->assertSame('name2', $collection[1]->getName());
    }

    public function testShouldFilterByCodeUsingEqual(): void
    {
        $this->createEntities();

        /** @var FilterableRepository $repository */
        $repository = $this->entityManager->getRepository(FilterableEntity::class);

        $collection = $repository->filterBy(['e:code' => '2'])->getQuery()->execute();

        $this->assertCount(1, $collection);
        $this->assertSame('name1', $collection[0]->getName());
    }

    private function createEntities(): void
    {
        foreach ([
            2 => 'name1',
            20 => 'name2',
            40 => 'otherValue',
        ] as $code => $name) {
            $entity = new FilterableEntity();
            $entity->setCode($code);
            $entity->setName($name);

            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }
}
