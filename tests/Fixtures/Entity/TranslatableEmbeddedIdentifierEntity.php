<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

#[Entity]
class TranslatableEmbeddedIdentifierEntity implements TranslatableInterface
{
    use TranslatableTrait;

    #[Embedded(class: TranslatableEmbeddableUuid::class, columnPrefix: false)]
    private TranslatableEmbeddableUuid $uuid;

    public function __construct()
    {
        $this->uuid = TranslatableEmbeddableUuid::random();
    }

    /**
     * @param mixed[] $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

    public function getUuid(): TranslatableEmbeddableUuid
    {
        return $this->uuid;
    }
}
