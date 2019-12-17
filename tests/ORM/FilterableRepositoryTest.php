<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\FilterableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Repository\FilterableRepository;

final class FilterableRepositoryTest extends AbstractBehaviorTestCase
{
    /**
     * @var FilterableRepository
     */
    private $filterableRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filterableRepository = static::$container->get(FilterableRepository::class);

        $this->createEntities();
    }

    public function testShouldFilterByNameUsingLike(): void
    {
        $collection = $this->filterableRepository->filterBy(['e:name' => 'name'])->getQuery()->execute();

        $this->assertCount(2, $collection);
        $this->assertSame('name1', $collection[0]->getName());
        $this->assertSame('name2', $collection[1]->getName());
    }

    public function testShouldFilterByCodeUsingEqual(): void
    {
        $collection = $this->filterableRepository->filterBy(['e:code' => '2'])->getQuery()->execute();

        $this->assertCount(1, $collection);
        $this->assertSame('name1', $collection[0]->getName());
    }

    private function createEntities(): void
    {
        $codeAndName = [
            2 => 'name1',
            20 => 'name2',
            40 => 'otherValue',
        ];

        foreach ($codeAndName as $code => $name) {
            $entity = new FilterableEntity();
            $entity->setCode($code);
            $entity->setName($name);
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }
}
