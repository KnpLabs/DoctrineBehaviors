<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

require_once 'DefaultTimestampableTest.php';

class RenamedTimestampableTest extends DefaultTimestampableTest
{
    protected function getTestedEntityClass()
    {
        return "\BehaviorFixtures\ORM\RenamedTimestampableEntity";
    }

    public function testCreate()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertInstanceOf('Datetime', $entity->getTraitCreatedAt());
        $this->assertInstanceOf('Datetime', $entity->getTraitUpdatedAt());

        $this->assertEquals(
            $entity->getTraitCreatedAt(),
            $entity->getTraitUpdatedAt(),
            'On creation, createdAt and updatedAt are the same'
        );
    }

    public function testUpdate()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedEntity();

        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $createdAt = $entity->getTraitCreatedAt();
        $em->clear();

        // wait for a second:
        sleep(1);

        $entity = $em->getRepository($this->getTestedEntityClass())->find($id);
        $entity->setTitle('test'); // need to modify at least one column to trigger onUpdate
        $em->flush();
        $em->clear();

        $entity = $em->getRepository($this->getTestedEntityClass())->find($id);
        $this->assertEquals($createdAt, $entity->getTraitCreatedAt(), 'createdAt is constant');

        $this->assertNotEquals(
            $entity->getTraitCreatedAt(),
            $entity->getTraitUpdatedAt(),
            'createat and updatedAt have diverged since new update'
        );
    }
}
