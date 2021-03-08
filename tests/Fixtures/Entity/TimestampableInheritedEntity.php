<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class TimestampableInheritedEntity extends AbstractTimestampableMappedSuperclassEntity
{
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $title;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
