<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\ORM\Translation;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Translatable\Translation;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\TranslatableCustomizedEntity;

/**
 * @ORM\Entity
 * Used to test translatable classes which declare a custom translation class.
 */
class TranslatableCustomizedEntityTranslation
{
    use Translation;

    /**
     * @ORM\Column(type="string")
     */
    private $title;

    public static function getTranslatableEntityClass()
    {
        return TranslatableCustomizedEntity::class;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title): void
    {
        $this->title = $title;
    }
}
