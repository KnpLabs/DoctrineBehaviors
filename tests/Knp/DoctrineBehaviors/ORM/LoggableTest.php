<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Doctrine\Common\EventManager;

require_once 'EntityManagerProvider.php';

class LoggableTest extends \PHPUnit_Framework_TestCase
{
    private $subscriber;
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
        $this->subscriber = new \Knp\DoctrineBehaviors\ORM\Loggable\LoggableSubscriber(
            new ClassAnalyzer(),
            false,
            $loggerCallback
        );

        $em->addEventSubscriber($this->subscriber);

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

        $this->assertCount(2, $this->logs);
        $this->assertEquals(
            $this->logs[0],
            'BehaviorFixtures\ORM\LoggableEntity #1 created'
        );
        $this->assertEquals(
            $this->logs[1],
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

        $this->assertCount(3, $this->logs);
        $this->assertEquals(
            $this->logs[2],
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

        $this->assertCount(2, $this->logs);
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

        $this->assertCount(3, $this->logs);
        $this->assertEquals(
            $this->logs[2],
            'BehaviorFixtures\ORM\LoggableEntity #1 removed'
        );
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function should_notice_deprecation()
    {
        set_error_handler(function() {throw new \Exception; }, E_USER_DEPRECATED);
        new \Knp\DoctrineBehaviors\ORM\Loggable\LoggableListener;
    }

    public function tearDown()
    {
        restore_error_handler();
    }
}
