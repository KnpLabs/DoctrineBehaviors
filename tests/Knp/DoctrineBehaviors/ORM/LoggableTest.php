<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Doctrine\Common\EventManager;

require_once 'EntityManagerProvider.php';

class LoggableTest extends \PHPUnit_Framework_TestCase
{
    private $listener;
    private $logs = [];

    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return [
            'BehaviorFixtures\\ORM\\LoggableEntity',
        ];
    }

    protected function getEventManager()
    {
        $em = new EventManager;
        $loggerCallback = function($message) {
            $this->logs[] = $message;
        };
        $this->listener = new \Knp\DoctrineBehaviors\ORM\Loggable\LoggableListener(
            $loggerCallback
        );

        $em->addEventSubscriber($this->listener);

        return $em;
    }

    /**
     * @test
     */
    public function should_log_changeset_message_when_created()
    {
        $em = $this->getEntityManager($this->getEventManager());

        $entity = new \BehaviorFixtures\ORM\LoggableEntity();
        $entity->setTitle('test');

        $em->persist($entity);
        $em->flush();

        $this->assertCount(1, $this->logs);
        $this->assertEquals(
            $this->logs[0],
            'BehaviorFixtures\ORM\LoggableEntity #1 : property "title" changed from "" to "test"'
        );
    }

    /**
     * @test
     */
    public function should_log_changeset_message_when_updated()
    {
        $em = $this->getEntityManager($this->getEventManager());

        $entity = new \BehaviorFixtures\ORM\LoggableEntity();

        $em->persist($entity);
        $em->flush();

        $entity->setTitle('test2');
        $em->flush();

        $this->assertCount(2, $this->logs);
        $this->assertEquals(
            $this->logs[1],
            'BehaviorFixtures\ORM\LoggableEntity #1 : property "title" changed from "" to "test2"'
        );
    }

    /**
     * @test
     */
    public function should_not_log_changeset_message_when_no_change()
    {
        $em = $this->getEntityManager($this->getEventManager());

        $entity = new \BehaviorFixtures\ORM\LoggableEntity();

        $em->persist($entity);
        $em->flush();

        $entity->setTitle('test2');
        $entity->setTitle(null);
        $em->flush();

        $this->assertCount(1, $this->logs);
    }

    /**
     * @test
     */
    public function should_log_removal_message_when_deleted()
    {
        $em = $this->getEntityManager($this->getEventManager());

        $entity = new \BehaviorFixtures\ORM\LoggableEntity();

        $em->persist($entity);
        $em->flush();

        $em->remove($entity);
        $em->flush();

        $this->assertCount(2, $this->logs);
        $this->assertEquals(
            $this->logs[1],
            'BehaviorFixtures\ORM\LoggableEntity #1 removed'
        );
    }
}
