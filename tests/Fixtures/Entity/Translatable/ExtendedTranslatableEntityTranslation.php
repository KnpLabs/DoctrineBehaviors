<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translatable;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Knp\DoctrineBehaviors\Exception\ShouldNotHappenException;

#[Entity]
class ExtendedTranslatableEntityTranslation extends AbstractTranslatableEntityTranslation
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string')]
    private ?string $extendedTitle = null;

    public function getExtendedTitle(): string
    {
        if ($this->extendedTitle === null) {
            throw new ShouldNotHappenException();
        }

        return $this->extendedTitle;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setExtendedTitle(string $title): void
    {
        $this->extendedTitle = $title;
    }
}
