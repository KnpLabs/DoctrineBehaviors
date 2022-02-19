<?php

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translatable;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Knp\DoctrineBehaviors\Exception\ShouldNotHappenException;

#[Entity]
class ExtendedTranslatableEntityWithJoinTableInheritance extends TranslatableEntityWithJoinTableInheritance
{
    #[Column(type: 'string')]
    private ?string $untranslatedField = null;

    /**
     * @return mixed
     * @throws ShouldNotHappenException
     */
    public function getUntranslatedField(): string
    {
        if ($this->untranslatedField === null) {
            throw new ShouldNotHappenException();
        }
        return $this->untranslatedField;
    }

    /**
     * @param mixed $untranslatedField
     */
    public function setUntranslatedField(String $untranslatedField): void
    {
        $this->untranslatedField = $untranslatedField;
    }
}