<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translatable;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

#[Entity]
#[InheritanceType(value: 'JOINED')]
#[DiscriminatorColumn(name: 'handle', type: 'string')]
class TranslatableEntityWithJoinTableInheritance implements TranslatableInterface
{
    use TranslatableTrait;

    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'AUTO')]
    private int $id;

    /**
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
