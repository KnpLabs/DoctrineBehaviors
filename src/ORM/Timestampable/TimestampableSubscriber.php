<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Timestampable;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

/**
 * Timestampable subscriber.
 *
 * Adds mapping to the timestampable entities.
 */
class TimestampableSubscriber implements EventSubscriber
{
    private $dbFieldType;

    public function __construct($dbFieldType)
    {
        $this->dbFieldType = $dbFieldType;
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if (is_subclass_of($classMetadata->getName(), 'Knp\DoctrineBehaviors\Model\Timestampable\TimestampableInterface')) {
            $classMetadata->addLifecycleCallback('updateTimestamps', Events::prePersist);
            $classMetadata->addLifecycleCallback('updateTimestamps', Events::preUpdate);

            foreach (array('createdAt', 'updatedAt') as $field) {
                if (!$classMetadata->hasField($field)) {
                    $classMetadata->mapField(array(
                        'fieldName' => $field,
                        'type'      => $this->dbFieldType,
                        'nullable'  => true,
                    ));
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }
}
