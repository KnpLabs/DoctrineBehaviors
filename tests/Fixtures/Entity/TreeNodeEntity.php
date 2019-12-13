<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use ArrayAccess;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Model\Tree\NodeInterface;
use Knp\DoctrineBehaviors\Model\Tree\NodeTrait;

/**
 * @ORM\Entity(repositoryClass="Knp\DoctrineBehaviors\Tests\Fixtures\Repository\TreeNodeRepository")
 */
class TreeNodeEntity implements NodeInterface, ArrayAccess
{
    use NodeTrait;

    /**
     * @var string
     */
    public const PATH_SEPARATOR = '/';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    protected $name;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     * @var int
     */
    protected $id;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
        $this->childNodes = new ArrayCollection();
    }

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
