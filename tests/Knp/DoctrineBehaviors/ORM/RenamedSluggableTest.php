<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

require_once 'DefaultSluggableTest.php';

class RenamedSluggableTest extends DefaultSluggableTest
{
    protected function getTestedEntityClass()
    {
        return "\BehaviorFixtures\ORM\RenamedSluggableEntity";
    }

    public function testSlugLoading()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedEntity();

        $expected = 'the-name';

        $entity->setName('The name');

        $em->persist($entity);
        $em->flush();

        $this->assertNotNull($id = $entity->getId());

        $em->clear();

        $entity = $em->getRepository($this->getTestedEntityClass())->find($id);

        $this->assertNotNull($entity);
        $this->assertEquals($entity->getTraitSlug(), $expected);
    }

    public function testNotUpdatedSlug()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedEntity();

        $expected = 'the-name';

        $entity->setName('The name');

        $em->persist($entity);
        $em->flush();

        $entity->setDate(new \DateTime);

        $em->persist($entity);
        $em->flush();

        $this->assertEquals($entity->getTraitSlug(), $expected);
    }

    public function testUpdatedSlug()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedEntity();

        $expected = 'the-name';

        $entity->setName('The name');

        $em->persist($entity);
        $em->flush();

        $this->assertEquals($entity->getTraitSlug(), $expected);

        $expected = 'the-name-2';

        $entity->setName('The name 2');

        $em->persist($entity);
        $em->flush();
        
        $this->assertEquals($entity->getTraitSlug(), $expected);
    }
}
