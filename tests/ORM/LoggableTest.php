<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Iterator;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\LoggableEntity;
use Psr\Log\Test\TestLogger;

final class LoggableTest extends AbstractBehaviorTestCase
{
    /**
     * @var TestLogger
     */
    private $testLogger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testLogger = static::$container->get(TestLogger::class);

        // reset for every run
        $this->testLogger->records = [];
        $this->testLogger->recordsByLevel = [];
    }

    public function testCreated(): void
    {
        $entity = new LoggableEntity();
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $expectedRecordCount = $this->isPostgreSql() ? 2 : 1;
        $this->assertCount($expectedRecordCount, $this->testLogger->records);

        $this->assertSame(
            sprintf('%s #1 created', LoggableEntity::class),
            $this->testLogger->records[0]['message']
        );
    }

    /**
     * @dataProvider dataProviderValues()
     */
    public function testShouldLogChangesetMessageWhenCreated(string $field, $value, string $expected): void
    {
        $entity = new LoggableEntity();

        $setterMethodName = 'set' . ucfirst($field);
        $entity->{$setterMethodName}($value);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->assertCount(2, $this->testLogger->records);

        $this->assertSame(
            sprintf('%s #1 created', LoggableEntity::class),
            $this->testLogger->records[0]['message']
        );

        $expectedMessage = sprintf(
            '%s #1 : property "%s" changed from "" to "%s"',
            LoggableEntity::class,
            $field,
            $expected
        );

        $this->assertStringContainsString($expectedMessage, $this->testLogger->records[1]['message']);
    }

    /**
     * @dataProvider dataProviderValues()
     */
    public function testShouldLogChangesetMessageWhenUpdated($field, $value, $expected): void
    {
        $entity = new LoggableEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $set = 'set' . ucfirst($field);

        $entity->{$set}($value);
        $this->entityManager->flush();

        $expectedRecordCount = $this->isPostgreSql() ? 3 : 2;
        $this->assertCount($expectedRecordCount, $this->testLogger->records);

        $lastRecord = array_pop($this->testLogger->records);

        $expectedMessage = sprintf(
            '%s #1 : property "%s" changed from "" to "%s"',
            LoggableEntity::class,
            $field,
            $expected
        );

        $this->assertSame($expectedMessage, $lastRecord['message']);
    }

    public function testShouldNotLogChangesetMessageWhenNoChange(): void
    {
        $entity = new LoggableEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $entity->setTitle('test2');
        $entity->setTitle(null);
        $this->entityManager->flush();

        $expectedRecordCount = $this->isPostgreSql() ? 2 : 1;
        $this->assertCount($expectedRecordCount, $this->testLogger->records);
    }

    public function testShouldLogRemovalMessageWhenDeleted(): void
    {
        $entity = new LoggableEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $expectedRecordCount = $this->isPostgreSql() ? 3 : 2;
        $this->assertCount($expectedRecordCount, $this->testLogger->records);

        $lastRecord = array_pop($this->testLogger->records);
        $this->assertSame(sprintf('%s #1 removed', LoggableEntity::class), $lastRecord['message']);
    }

    public function dataProviderValues(): Iterator
    {
        yield ['title', 'test', 'test'];
        yield ['roles', ['x' => 'y'], 'an array'];
    }
}
