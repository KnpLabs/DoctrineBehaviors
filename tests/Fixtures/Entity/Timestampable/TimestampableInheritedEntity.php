<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Timestampable;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Knp\DoctrineBehaviors\Exception\ShouldNotHappenException;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\AbstractTimestampableMappedSuperclassEntity;

#[Entity]
class TimestampableInheritedEntity extends AbstractTimestampableMappedSuperclassEntity
{
    #[Column(type: 'string', nullable: true)]
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
