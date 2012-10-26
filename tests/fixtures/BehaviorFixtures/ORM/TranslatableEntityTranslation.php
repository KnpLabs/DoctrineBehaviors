<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ORM\Entity
 * @ODM\EmbeddedDocument
 */
class TranslatableEntityTranslation
{
    use Model\Translatable\Translation;

    /**
     * @ORM\Column(type="string")
     * @ODM\String
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
