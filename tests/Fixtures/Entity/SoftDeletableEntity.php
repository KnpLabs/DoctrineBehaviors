<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\SoftDeletableInterface;
use Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletableTrait;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorMap({
 *     "mainclass" = "Knp\DoctrineBehaviors\Tests\Fixtures\Entity\SoftDeletableEntity",
 *     "subclass" = "Knp\DoctrineBehaviors\Tests\Fixtures\Entity\SoftDeletableEntityInherit"
 * })
 */
class SoftDeletableEntity implements SoftDeletableInterface
{
    use SoftDeletableTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    public function getId(): int
    {
        return $this->id;
    }
}
