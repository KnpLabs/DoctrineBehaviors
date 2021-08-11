<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Timestampable;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Exception\ShouldNotHappenException;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\AbstractTimestampableMappedSuperclassEntity;

/**
 * @ORM\Entity
 */
class TimestampableInheritedEntity extends AbstractTimestampableMappedSuperclassEntity
{
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $title = null;

    public function getTitle(): string
    {
        if ($this->title === null) {
            throw new ShouldNotHappenException();
        }

        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
