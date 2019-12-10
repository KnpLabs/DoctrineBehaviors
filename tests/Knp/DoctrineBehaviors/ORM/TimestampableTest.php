<?php

declare(strict_types=1);

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Doctrine\Common\EventManager;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

require_once 'EntityManagerProvider.php';

class TimestampableTest extends \PHPUnit\Framework\TestCase
{
    use EntityManagerProvider;

    public function testItShouldInitializeCreateAndUpdateDatetimeWhenCreated(): void
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\TimestampableEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertInstanceOf('Datetime', $entity->getCreatedAt());
        $this->assertInstanceOf('Datetime', $entity->getUpdatedAt());

        $this->assertSame(
            $entity->getCreatedAt(),
            $entity->getUpdatedAt(),
            'On creation, createdAt and updatedAt are the same'
        );
    }

    public function testItShouldModifyUpdateDatetimeWhenUpdatedButNotTheCreationDatetime(): void
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\TimestampableEntity();

        $em->persist($entity);
        $em->flush();
        $em->refresh($entity);
        $id = $entity->getId();
        $createdAt = $entity->getCreatedAt();
        $em->clear();

        // wait for a second:
        sleep(1);

        $entity = $em->getRepository('BehaviorFixtures\ORM\TimestampableEntity')->find($id);
        $entity->setTitle('test');
        $em->flush();
        $em->clear();

        $entity = $em->getRepository('BehaviorFixtures\ORM\TimestampableEntity')->find($id);
        $this->assertSame($createdAt, $entity->getCreatedAt(), 'createdAt is constant');

        $this->assertNotSame(
            $entity->getCreatedAt(),
            $entity->getUpdatedAt(),
            'createat and updatedAt have diverged since new update'
        );
    }

    public function testItShouldReturnTheSameDatetimeWhenNotUpdated(): void
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\TimestampableEntity();

        $em->persist($entity);
        $em->flush();
        $em->refresh($entity);
        $id = $entity->getId();
        $createdAt = $entity->getCreatedAt();
        $updatedAt = $entity->getUpdatedAt();
        $em->clear();

        sleep(1);

        $entity = $em->getRepository('BehaviorFixtures\ORM\TimestampableEntity')->find($id);
        $em->persist($entity);
        $em->flush();
        $em->clear();

        $this->assertSame(
            $entity->getCreatedAt(),
            $createdAt,
            'Creation timestamp has changed'
        );

        $this->assertSame(
            $entity->getUpdatedAt(),
            $updatedAt,
            'Update timestamp has changed'
        );
    }

    public function testItShouldModifyUpdateDatetimeOnlyOnce(): void
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\TimestampableEntity();

        $em->persist($entity);
        $em->flush();
        $em->refresh($entity);
        $id = $entity->getId();
        $createdAt = $entity->getCreatedAt();
        $em->clear();

        sleep(1);

        $entity = $em->getRepository('BehaviorFixtures\ORM\TimestampableEntity')->find($id);
        $entity->setTitle('test');
        $em->flush();
        $updatedAt = $entity->getUpdatedAt();

        sleep(1);

        $em->flush();

        $this->assertSame(
            $entity->getCreatedAt(),
            $createdAt,
            'Creation timestamp has changed'
        );

        $this->assertSame(
            $entity->getUpdatedAt(),
            $updatedAt,
            'Update timestamp has changed'
        );
    }

    protected function getUsedEntityFixtures()
    {
        return [
            'BehaviorFixtures\\ORM\\TimestampableEntity',
        ];
    }

    protected function getEventManager()
    {
        $em = new EventManager();

        $em->addEventSubscriber(
            new \Knp\DoctrineBehaviors\ORM\Timestampable\TimestampableSubscriber(
                new ClassAnalyzer(),
                false,
                'Knp\DoctrineBehaviors\Model\Timestampable\Timestampable',
                'datetime'
            )
        );

        return $em;
    }
}
