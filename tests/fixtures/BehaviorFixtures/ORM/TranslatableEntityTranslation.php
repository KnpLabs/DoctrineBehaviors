<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\ORM as OrmBehaviors;

/**
 * @ORM\Entity
 */
class TranslatableEntityTranslation
{
    use OrmBehaviors\Translatable\Translation;

    /**
     * @ORM\Column(type="string")
     */
    private $title;

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }
}
