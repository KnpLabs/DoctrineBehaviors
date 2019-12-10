<?php

declare(strict_types=1);

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Doctrine\Common\EventManager;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

require_once 'EntityManagerProvider.php';

class SoftDeletableTest extends \PHPUnit\Framework\TestCase
{
    use EntityManagerProvider;

    public function testDelete(): void
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\DeletableEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertNotNull($id = $entity->getId());
        $this->assertFalse($entity->isDeleted());

        $em->remove($entity);
        $em->flush();
        $em->clear();

        $entity = $em->getRepository('BehaviorFixtures\ORM\DeletableEntity')->find($id);

        $this->assertNotNull($entity);
        $this->assertTrue($entity->isDeleted());
    }

    public function testPostDelete(): void
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\DeletableEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertNotNull($id = $entity->getId());

        $entity->setDeletedAt((new \DateTime())->modify('+1 day'));

        $em->flush();
        $em->clear();

        $entity = $em->getRepository('BehaviorFixtures\ORM\DeletableEntity')->find($id);

        $this->assertNotNull($entity);
        $this->assertFalse($entity->isDeleted());
        $this->assertTrue($entity->willBeDeleted());
        $this->assertTrue($entity->willBeDeleted((new \DateTime())->modify('+2 day')));
        $this->assertFalse($entity->willBeDeleted((new \DateTime())->modify('+12 hour')));

        $entity->setDeletedAt((new \DateTime())->modify('-1 day'));

        $em->flush();
        $em->clear();

        $entity = $em->getRepository('BehaviorFixtures\ORM\DeletableEntity')->find($id);

        $this->assertNotNull($entity);
        $this->assertTrue($entity->isDeleted());
    }

    public function testDeleteInheritance(): void
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\DeletableEntityInherit();

        $em->persist($entity);
        $em->flush();

        $em->remove($entity);
        $em->flush();

        $this->assertTrue($entity->isDeleted());
    }

    public function testRestore(): void
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\DeletableEntityInherit();

        $em->persist($entity);
        $em->flush();

        $em->remove($entity);
        $em->flush();

        $this->assertTrue($entity->isDeleted());

        $entity->restore();

        $this->assertFalse($entity->isDeleted());
    }

    protected function getUsedEntityFixtures()
    {
        return [
            'BehaviorFixtures\\ORM\\DeletableEntity',
        ];
    }

    protected function getEventManager()
    {
        $em = new EventManager();

        $em->addEventSubscriber(
            new \Knp\DoctrineBehaviors\ORM\SoftDeletable\SoftDeletableSubscriber(
                new ClassAnalyzer(),
                true,
                'Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletable'
            )
        );

        return $em;
    }
}
