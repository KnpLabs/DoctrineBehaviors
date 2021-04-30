<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use DateTime;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\TimestampableInheritedEntity;

final class TimestampableWithInheritanceTest extends AbstractBehaviorTestCase
{
    public function testItShouldInitializeCreateAndUpdateDatetimeWhenCreated(): void
    {
        $timestampableInheritedEntity = new TimestampableInheritedEntity();

        $this->entityManager->persist($timestampableInheritedEntity);
        $this->entityManager->flush();

        self::assertInstanceOf(Datetime::class, $timestampableInheritedEntity->getCreatedAt());
        self::assertInstanceOf(Datetime::class, $timestampableInheritedEntity->getUpdatedAt());
        self::assertSame(
            $timestampableInheritedEntity->getCreatedAt(),
            $timestampableInheritedEntity->getUpdatedAt(),
            'On creation, createdAt and updatedAt are the same'
        );
    }
}
