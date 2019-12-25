<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use ArrayAccess;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;
use Knp\DoctrineBehaviors\Model\Tree\TreeNodeTrait;

/**
 * @ORM\Entity(repositoryClass="Knp\DoctrineBehaviors\Tests\Fixtures\Repository\TreeNodeRepository")
 */
class TreeNodeEntity implements TreeNodeInterface, ArrayAccess
{
    use TreeNodeTrait;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $name;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     * @var int|null
     */
    private $id;

    public function __toString(): string
    {
        return (string) $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
