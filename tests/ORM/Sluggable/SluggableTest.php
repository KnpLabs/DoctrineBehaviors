<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM\Sluggable;

use DateTime;
use Doctrine\Persistence\ObjectRepository;
use Iterator;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Sluggable\SluggableEntity;

final class SluggableTest extends AbstractBehaviorTestCase
{
    /**
     * @var ObjectRepository<SluggableEntity>
     */
    private ObjectRepository $sluggableRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sluggableRepository = $this->entityManager->getRepository(SluggableEntity::class);
    }

    public function testSlugLoading(): void
    {
        $entity = new SluggableEntity();
        $entity->setName('The name');

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $this->assertNotNull($id);

        $this->entityManager->clear();

        /** @var SluggableEntity $entity */
        $entity = $this->sluggableRepository->find($id);

        $this->assertNotNull($entity);
        $this->assertSame('the-name', $entity->getSlug());
    }

    /**
     * @dataProvider provideDataForTest
     */
    public function testNotUpdatedSlug(string $value, string $expectedSlug): void
    {
        $sluggableEntity = new SluggableEntity();
        $sluggableEntity->setName($value);

        $this->entityManager->persist($sluggableEntity);
        $this->entityManager->flush();

        $sluggableEntity->setDate(new DateTime());

        $this->entityManager->persist($sluggableEntity);
        $this->entityManager->flush();

        $this->assertSame($expectedSlug, $sluggableEntity->getSlug());
    }

    public static function provideDataForTest(): Iterator
    {
        yield ['The name', 'the-name'];
        yield ['Löic & René', 'loic-rene'];
        yield ['Иван Иванович', 'ivan-ivanovic'];
        yield ['Châteauneuf du Pape', 'chateauneuf-du-pape'];
        yield ['Žluťoučký kůň', 'zlutoucky-kun'];
    }

    public function testUpdatedSlug(): void
    {
        $sluggableEntity = new SluggableEntity();
        $sluggableEntity->setName('The name');

        $this->entityManager->persist($sluggableEntity);
        $this->entityManager->flush();

        $this->assertSame('the-name', $sluggableEntity->getSlug());

        $sluggableEntity->setName('The name 2');

        $this->entityManager->persist($sluggableEntity);
        $this->entityManager->flush();

        $this->assertSame('the-name-2', $sluggableEntity->getSlug());
    }
}
