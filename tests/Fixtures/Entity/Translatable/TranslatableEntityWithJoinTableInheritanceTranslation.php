<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translatable;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Exception\ShouldNotHappenException;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

#[Entity]
#[InheritanceType(value: 'JOINED')]
#[DiscriminatorColumn(name: 'handle', type: 'string')]
class TranslatableEntityWithJoinTableInheritanceTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string')]
    private ?string $title = null;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function getTitle(): string
    {
        if ($this->title === null) {
            throw new ShouldNotHappenException();
        }

        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
