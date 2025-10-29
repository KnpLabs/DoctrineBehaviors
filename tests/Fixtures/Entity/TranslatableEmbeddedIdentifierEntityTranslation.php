<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

#[Entity]
class TranslatableEmbeddedIdentifierEntityTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[Embedded(class: TranslatableEmbeddableUuid::class, columnPrefix: false)]
    private TranslatableEmbeddableUuid $uuid;

    #[Column(type: 'string')]
    private ?string $title = null;

    public function __construct()
    {
        $this->uuid = TranslatableEmbeddableUuid::random();
    }

    public function getUuid(): TranslatableEmbeddableUuid
    {
        return $this->uuid;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
