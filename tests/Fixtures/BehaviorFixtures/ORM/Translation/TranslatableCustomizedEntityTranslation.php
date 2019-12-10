<?php

declare(strict_types=1);

namespace BehaviorFixtures\ORM\Translation;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model;

/**
 * @ORM\Entity
 * Used to test translatable classes which declare a custom translation class.
 */
class TranslatableCustomizedEntityTranslation
{
    use Model\Translatable\Translation;

    /**
     * @ORM\Column(type="string")
     */
    private $title;

    public static function getTranslatableEntityClass()
    {
        return '\BehaviorFixtures\ORM\TranslatableCustomizedEntity';
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
