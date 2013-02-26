<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

require_once 'DefaultSoftDeletableTest.php';

class RenamedSoftDeletableTest extends DefaultSoftDeletableTest
{
    protected function getTestedEntityClass()
    {
        return "\BehaviorFixtures\ORM\RenamedDeletableEntity";
    }

    public function testDelete()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertNotNull($id = $entity->getId());
        $this->assertFalse($entity->isTraitDeleted());

        $em->remove($entity);
        $em->flush();
        $em->clear();

        $entity = $em->getRepository($this->getTestedEntityClass())->find($id);

        $this->assertNotNull($entity);
        $this->assertTrue($entity->isTraitDeleted());
    }

    public function testPostDelete()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertNotNull($id = $entity->getId());

        $entity->setTraitDeletedAt((new \DateTime())->modify('+1 day'));

        $em->flush();
        $em->clear();

        $entity = $em->getRepository($this->getTestedEntityClass())->find($id);

        $this->assertNotNull($entity);
        $this->assertFalse($entity->isTraitDeleted());
        $this->assertTrue($entity->willTraitBeDeleted());
        $this->assertTrue($entity->willTraitBeDeleted((new \DateTime())->modify('+2 day')));
        $this->assertFalse($entity->willTraitBeDeleted((new \DateTime())->modify('+12 hour')));

        $entity->setTraitDeletedAt((new \DateTime())->modify('-1 day'));

        $em->flush();
        $em->clear();

        $entity = $em->getRepository($this->getTestedEntityClass())->find($id);

        $this->assertNotNull($entity);
        $this->assertTrue($entity->isTraitDeleted());
    }
}
