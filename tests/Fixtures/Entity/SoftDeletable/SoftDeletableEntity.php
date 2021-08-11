<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity\SoftDeletable;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\SoftDeletableInterface;
use Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletableTrait;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorMap({
 *     "mainclass" = "Knp\DoctrineBehaviors\Tests\Fixtures\Entity\SoftDeletable\SoftDeletableEntity",
 *     "subclass" = "Knp\DoctrineBehaviors\Tests\Fixtures\Entity\SoftDeletable\SoftDeletableEntityInherit"
 * })
 */
class SoftDeletableEntity implements SoftDeletableInterface
{
    use SoftDeletableTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
