<?php

declare(strict_types=1);

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Doctrine\Common\EventManager;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

require_once 'EntityManagerProvider.php';

class LoggableTest extends \PHPUnit\Framework\TestCase
{
    use EntityManagerProvider;

    private $subscriber;

    private $logs = [];

    /**
     * @dataProvider dataProviderValues
     */
    public function testShouldLogChangesetMessageWhenCreated($field, $value, $expected): void
    {
        $em = $this->getEntityManager($this->getEventManager());

        $entity = new \BehaviorFixtures\ORM\LoggableEntity();

        $set = 'set' . ucfirst($field);

        $entity->{$set}($value);

        $em->persist($entity);
        $em->flush();

        $this->assertCount(2, $this->logs);
        $this->assertSame(
            $this->logs[0],
            'BehaviorFixtures\ORM\LoggableEntity #1 created'
        );

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
        $em = $this->getEntityManager($this->getEventManager());

        $entity = new \BehaviorFixtures\ORM\LoggableEntity();

        $em->persist($entity);
        $em->flush();

        $set = 'set' . ucfirst($field);

        $entity->{$set}($value);
        $em->flush();

        $this->assertCount(3, $this->logs);
        $this->assertSame(
            $this->logs[2],
            'BehaviorFixtures\ORM\LoggableEntity #1 : property "' . $field . '" changed from "" to "' . $expected . '"'
        );
    }

    public function testShouldNotLogChangesetMessageWhenNoChange(): void
    {
        $em = $this->getEntityManager($this->getEventManager());

        $entity = new \BehaviorFixtures\ORM\LoggableEntity();

        $em->persist($entity);
        $em->flush();

        $entity->setTitle('test2');
        $entity->setTitle(null);
        $em->flush();

        $this->assertCount(2, $this->logs);
    }

    public function testShouldLogRemovalMessageWhenDeleted(): void
    {
        $em = $this->getEntityManager($this->getEventManager());

        $entity = new \BehaviorFixtures\ORM\LoggableEntity();

        $em->persist($entity);
        $em->flush();

        $em->remove($entity);
        $em->flush();

        $this->assertCount(3, $this->logs);
        $this->assertSame(
            $this->logs[2],
            'BehaviorFixtures\ORM\LoggableEntity #1 removed'
        );
    }

    public function dataProviderValues()
    {
        return [
            [
                'title', 'test', 'test',
            ],
            [
                'roles', ['x' => 'y'], 'an array',
            ],
            [
                'date', new \DateTime('2014-02-02 12:20:30.000010'), '2014-02-02 12:20:30.000010',
            ],
        ];
    }

    protected function getUsedEntityFixtures()
    {
        return [
            'BehaviorFixtures\\ORM\\LoggableEntity',
        ];
    }

    protected function getEventManager()
    {
        $em = new EventManager();
        $loggerCallback = function ($message): void {
            $this->logs[] = $message;
        };
        $this->subscriber = new \Knp\DoctrineBehaviors\ORM\Loggable\LoggableSubscriber(
            new ClassAnalyzer(),
            false,
            $loggerCallback
        );

        $em->addEventSubscriber($this->subscriber);

        return $em;
    }
}
