<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;

/**
 * When console make:entity creates a new class, the event arguments are not fully populated
 *
 * This test emulates the event dispatch near the end of method
 *   Doctrine\ORM\Mapping\ClassMetadataFactory::doLoadMetadata()
 */
final class TimestampableMakeEntityTest extends AbstractBehaviorTestCase
{
    public function testMakeEntityEmptyEvent(): void
    {
        $className = 'App\Entity\MyClass';
        $classMetadata = new ClassMetadata($className);
        $loadClassMetadataEventArgs = new LoadClassMetadataEventArgs($classMetadata, $this->entityManager);

        $doctrineEventManager = $this->entityManager->getEventManager();
        $doctrineEventManager->dispatchEvent(Events::loadClassMetadata, $loadClassMetadataEventArgs);
        $this->expectNotToPerformAssertions();
    }
}
