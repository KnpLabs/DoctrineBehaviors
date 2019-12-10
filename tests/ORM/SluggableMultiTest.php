<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use DateTime;
use Doctrine\Common\EventManager;
use Knp\DoctrineBehaviors\Model\Sluggable\Sluggable;
use Knp\DoctrineBehaviors\ORM\Sluggable\SluggableSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\SluggableMultiEntity;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/EntityManagerProvider.php';

class SluggableMultiTest extends TestCase
{
    use EntityManagerProvider;

    public function testSlugLoading(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new SluggableMultiEntity();

        $expected = 'the+name+title';

        $entity->setName('The name');

        $entityManager->persist($entity);
        $entityManager->flush();

        $this->assertNotNull($id = $entity->getId());

        $entityManager->clear();

        $entity = $entityManager->getRepository(SluggableMultiEntity::class)->find($id);

        $this->assertNotNull($entity);
        $this->assertSame($entity->getSlug(), $expected);
    }

    public function testNotUpdatedSlug(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new SluggableMultiEntity();

        $expected = 'the+name+title';

        $entity->setName('The name');

        $entityManager->persist($entity);
        $entityManager->flush();

        $entity->setDate(new DateTime());

        $entityManager->persist($entity);
        $entityManager->flush();

        $this->assertSame($entity->getSlug(), $expected);
    }

    public function testUpdatedSlug(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new SluggableMultiEntity();

        $expected = 'the+name+title';

        $entity->setName('The name');

        $entityManager->persist($entity);
        $entityManager->flush();

        $this->assertSame($entity->getSlug(), $expected);

        $expected = 'the+name+2+title';

        $entity->setName('The name 2');

        $entityManager->persist($entity);
        $entityManager->flush();

        $this->assertSame($entity->getSlug(), $expected);
    }

    protected function getUsedEntityFixtures()
    {
        return [SluggableMultiEntity::class];
    }

    protected function getEventManager(): EventManager
    {
        $eventManager = new EventManager();

        $eventManager->addEventSubscriber(new SluggableSubscriber(new ClassAnalyzer(), false, Sluggable::class));

        return $eventManager;
    }
}
