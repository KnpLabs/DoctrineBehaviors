<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Doctrine\Common\EventManager;

require_once 'EntityManagerProvider.php';

class DefaultLoggableTest extends \PHPUnit_Framework_TestCase
{
    private $listener;
    private $logs = [];

    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return array(
            $this->getTestedEntityClass()
        );
    }

    protected function getTestedEntityClass()
    {
        return "\BehaviorFixtures\ORM\DefaultLoggableEntity";
    }

    protected function getTestedEntity()
    {
        $class = $this->getTestedEntityClass();
        return new $class;
    }

    protected function getEventManager()
    {
        $em = new EventManager;
        $loggerCallback = function($message) {
            $this->logs[] = $message;
        };
        $this->listener = new \Knp\DoctrineBehaviors\ORM\Loggable\LoggableListener(
            new ClassAnalyzer(),
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

        $entity = $this->getTestedEntity();
        $entity->setTitle('test');

        $em->persist($entity);
        $em->flush();

        $this->assertCount(1, $this->logs);
        $this->assertEquals(
            $this->logs[0],
            substr($this->getTestedEntityClass(), 1) . ' #1 : property "title" changed from "" to "test"'
        );
    }

    /**
     * @test
     */
    public function should_log_changeset_message_when_updated()
    {
        $em = $this->getEntityManager($this->getEventManager());

        $entity = $this->getTestedEntity();

        $em->persist($entity);
        $em->flush();

        $entity->setTitle('test2');
        $em->flush();

        $this->assertCount(2, $this->logs);
        $this->assertEquals(
            $this->logs[1],
            substr($this->getTestedEntityClass(), 1) . ' #1 : property "title" changed from "" to "test2"'
        );
    }

    /**
     * @test
     */
    public function should_not_log_changeset_message_when_no_change()
    {
        $em = $this->getEntityManager($this->getEventManager());

        $entity = $this->getTestedEntity();

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

        $entity = $this->getTestedEntity();

        $em->persist($entity);
        $em->flush();

        $em->remove($entity);
        $em->flush();

        $this->assertCount(2, $this->logs);
        $this->assertEquals(
            $this->logs[1],
            substr($this->getTestedEntityClass(), 1) . ' #1 removed'
        );
    }
}
