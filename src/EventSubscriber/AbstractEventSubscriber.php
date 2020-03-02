<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use function class_exists;
use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Mapping\ClassMetadata;

/**
 * @todo Make it implement EventSubscriberInterface from DoctrineBundle and DoctrineMongoDBBundle ?
 */
abstract class AbstractEventSubscriber implements EventSubscriber
{
    final protected function handlesORMEvents(): bool
    {
        return class_exists('Doctrine\ORM\Events');
    }

    final protected function handlesMongoODMEvents(): bool
    {
        return class_exists('Doctrine\ODM\MongoDB\Events');
    }

    final protected function isORMObject(ClassMetadata $classMetadata): bool
    {
        return $classMetadata instanceof Doctrine\ORM\Mapping\ClassMetadata;
    }

    final protected function isMongoODMObject(ClassMetadata $classMetadata): bool
    {
        return $classMetadata instanceof Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
    }
}
