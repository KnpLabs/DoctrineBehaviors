<?php

declare(strict_types=1);

namespace Tests\Knp\DoctrineBehaviors\ORM;

use BehaviorFixtures\ORM\LoggableEntity;
use DateTime;
use Doctrine\Common\EventManager;
use Knp\DoctrineBehaviors\ORM\Loggable\LoggableSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/EntityManagerProvider.php';

class LoggableTest extends TestCase
{
    use EntityManagerProvider;

    private $subscriber;

    private $logs = [];

    /**
     * @dataProvider dataProviderValues
     */
    public function testShouldLogChangesetMessageWhenCreated($field, $value, $expected): void
    {
        $entityManager = $this->getEntityManager($this->getEventManager());

        $entity = new LoggableEntity();

        $set = 'set' . ucfirst($field);

        $entity->{$set}($value);

        $entityManager->persist($entity);
        $entityManager->flush();

        $this->assertCount(2, $this->logs);
        $this->assertSame($this->logs[0], 'BehaviorFixtures\ORM\LoggableEntity #1 created');

        $this->assertSame(
            $this->logs[1],
            'BehaviorFixtures\ORM\LoggableEntity #1 : property "' . $field . '" changed from "" to "' . $expected . '"'
        );
    }

    /**
     * @dataProvider dataProviderValues
     */
    public function testShouldLogChangesetMessageWhenUpdated($field, $value, $expected): void
    {
        $entityManager = $this->getEntityManager($this->getEventManager());

        $entity = new LoggableEntity();

        $entityManager->persist($entity);
        $entityManager->flush();

        $set = 'set' . ucfirst($field);

        $entity->{$set}($value);
        $entityManager->flush();

        $this->assertCount(3, $this->logs);
        $this->assertSame(
            $this->logs[2],
            'BehaviorFixtures\ORM\LoggableEntity #1 : property "' . $field . '" changed from "" to "' . $expected . '"'
        );
    }

    public function testShouldNotLogChangesetMessageWhenNoChange(): void
    {
        $entityManager = $this->getEntityManager($this->getEventManager());

        $entity = new LoggableEntity();

        $entityManager->persist($entity);
        $entityManager->flush();

        $entity->setTitle('test2');
        $entity->setTitle(null);
        $entityManager->flush();

        $this->assertCount(2, $this->logs);
    }

    public function testShouldLogRemovalMessageWhenDeleted(): void
    {
        $entityManager = $this->getEntityManager($this->getEventManager());

        $entity = new LoggableEntity();

        $entityManager->persist($entity);
        $entityManager->flush();

        $entityManager->remove($entity);
        $entityManager->flush();

        $this->assertCount(3, $this->logs);
        $this->assertSame($this->logs[2], 'BehaviorFixtures\ORM\LoggableEntity #1 removed');
    }

    public function dataProviderValues()
    {
        return [
            ['title', 'test', 'test'],
            [
                'roles', ['x' => 'y'], 'an array',
            ],
            ['date', new DateTime('2014-02-02 12:20:30.000010'), '2014-02-02 12:20:30.000010'],
        ];
    }

    protected function getUsedEntityFixtures()
    {
        return [LoggableEntity::class];
    }

    protected function getEventManager(): EventManager
    {
        $eventManager = new EventManager();
        $loggerCallback = function ($message): void {
            $this->logs[] = $message;
        };
        $this->subscriber = new LoggableSubscriber(new ClassAnalyzer(), false, $loggerCallback);

        $eventManager->addEventSubscriber($this->subscriber);

        return $eventManager;
    }
}
