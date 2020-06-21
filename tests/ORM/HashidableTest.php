<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\HashidableEntity;

final class HashidableTest extends AbstractBehaviorTestCase
{
    public function testAutoSettingHashId(): void
    {
        $entity = new HashidableEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $this->assertNotNull($id);
        $this->assertSame(1, $id);

        $hashId = $entity->getHashId();
        $this->assertSame('jR', $hashId);

        $this->entityManager->clear();
    }
}
