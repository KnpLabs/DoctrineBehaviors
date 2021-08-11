<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity\SoftDeletable;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SoftDeletableEntityInherit extends SoftDeletableEntity
{
    /**
     * @ORM\Column(type="string")
     */
    private string $name;

    public function getName(): string
    {
        return $this->name;
    }
}
