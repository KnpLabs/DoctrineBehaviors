<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Sluggable;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Exception\ShouldNotHappenException;
use Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait;

#[Entity]
class SluggableWithoutRegenerateEntity implements SluggableInterface
{
    use SluggableTrait;

    #[Column(type: 'string')]
    private ?string $name = null;

    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        if ($this->name === null) {
            throw new ShouldNotHappenException();
        }

        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string[]
     */
    public function getSluggableFields(): array
    {
        return ['name'];
    }

    /**
     * Private access by trait
     */
    protected function shouldRegenerateSlugOnUpdate(): bool
    {
        return false;
    }
}
