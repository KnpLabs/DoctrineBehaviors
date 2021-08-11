<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translatable;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

#[MappedSuperclass]
abstract class AbstractTranslatableEntity implements TranslatableInterface
{
    use TranslatableTrait;

    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }
}
