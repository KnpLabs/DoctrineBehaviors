<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use DateTime;
use Doctrine\Common\EventManager;
use Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletable;
use Knp\DoctrineBehaviors\ORM\SoftDeletable\SoftDeletableSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\DeletableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\DeletableEntityInherit;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/EntityManagerProvider.php';

class SoftDeletableTest extends TestCase
{
    use EntityManagerProvider;

    public function testDelete(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new DeletableEntity();

        $entityManager->persist($entity);
        $entityManager->flush();

        $this->assertNotNull($id = $entity->getId());
        $this->assertFalse($entity->isDeleted());

        $entityManager->remove($entity);
        $entityManager->flush();
        $entityManager->clear();

        $entity = $entityManager->getRepository(DeletableEntity::class)->find($id);

        $this->assertNotNull($entity);
        $this->assertTrue($entity->isDeleted());
    }

    public function testPostDelete(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new DeletableEntity();

        $entityManager->persist($entity);
        $entityManager->flush();

        $this->assertNotNull($id = $entity->getId());

        $entity->setDeletedAt((new DateTime())->modify('+1 day'));

        $entityManager->flush();
        $entityManager->clear();

        $entity = $entityManager->getRepository(DeletableEntity::class)->find($id);

        $this->assertNotNull($entity);
        $this->assertFalse($entity->isDeleted());
        $this->assertTrue($entity->willBeDeleted());
        $this->assertTrue($entity->willBeDeleted((new DateTime())->modify('+2 day')));
        $this->assertFalse($entity->willBeDeleted((new DateTime())->modify('+12 hour')));

        $entity->setDeletedAt((new DateTime())->modify('-1 day'));

        $entityManager->flush();
        $entityManager->clear();

        $entity = $entityManager->getRepository(DeletableEntity::class)->find($id);

        $this->assertNotNull($entity);
        $this->assertTrue($entity->isDeleted());
    }

    public function testDeleteInheritance(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new DeletableEntityInherit();

        $entityManager->persist($entity);
        $entityManager->flush();

        $entityManager->remove($entity);
        $entityManager->flush();

        $this->assertTrue($entity->isDeleted());
    }

    public function testRestore(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new DeletableEntityInherit();

        $entityManager->persist($entity);
        $entityManager->flush();

        $entityManager->remove($entity);
        $entityManager->flush();

        $this->assertTrue($entity->isDeleted());

        $entity->restore();

        $this->assertFalse($entity->isDeleted());
    }

    protected function getUsedEntityFixtures()
    {
        return [DeletableEntity::class];
    }

    protected function getEventManager(): EventManager
    {
        $eventManager = new EventManager();

        $eventManager->addEventSubscriber(new SoftDeletableSubscriber(new ClassAnalyzer(), true, SoftDeletable::class));

        return $eventManager;
    }
}
