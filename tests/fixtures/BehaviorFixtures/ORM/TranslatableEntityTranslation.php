<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model;

/**
 * @ORM\Entity
 */
class TranslatableEntityTranslation implements Model\Translatable\TranslationInterface
{
    use Model\Translatable\Translation;

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
