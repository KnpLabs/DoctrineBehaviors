<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SoftDeletableEntityInherit extends SoftDeletableEntity
{
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $name;

    public function getName(): string
    {
        return $this->name;
    }
}
