<?php

declare(strict_types=1);

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Sortable;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

use Doctrine\ORM\Events;

use Doctrine\ORM\Mapping\ClassMetadata;
use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

class SortableSubscriber extends AbstractSubscriber
{
    /**
     * @var string
     */
    private $sortableTrait;

    public function __construct(ClassAnalyzer $classAnalyzer, bool $isRecursive, string $sortableTrait)
    {
        parent::__construct($classAnalyzer, $isRecursive);

        $this->sortableTrait = $sortableTrait;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if ($this->isSortable($classMetadata)) {
            if (!$classMetadata->hasField('sort')) {
                $classMetadata->mapField([
                    'fieldName' => 'sort',
                    'type' => 'integer'
                ]);
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }

    private function isSortable(ClassMetadata $classMetadata): bool
    {
        return $this->getClassAnalyzer()->hasTrait(
            $classMetadata->reflClass,
            $this->sortableTrait,
            $this->isRecursive
        );
    }
}
