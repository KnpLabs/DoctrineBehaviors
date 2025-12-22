<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use ArrayAccess;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;
use Knp\DoctrineBehaviors\Model\Tree\TreeNodeTrait;
use Knp\DoctrineBehaviors\Tests\Fixtures\Repository\TreeNodeRepository;
use Stringable;

#[Entity(repositoryClass: TreeNodeRepository::class)]
class TreeNodeEntity implements TreeNodeInterface, ArrayAccess, Stringable
{
    use TreeNodeTrait;

    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'NONE')]
    private ?int $id = null;

    #[Column(type: 'string', length: 255, nullable: true)]
    private ?string $name = null;

    private string $parentNodePath;

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
