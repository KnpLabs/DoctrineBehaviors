<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model;

/**
 * @ORM\Entity
 */
class ExtendedTranslatableEntityTranslation extends AbstractTranslatableEntityTranslation
{
    /**
     * @ORM\Column(type="string")
     */
    private $extendedTitle;

    public function getExtendedTitle()
    {
        return $this->extendedTitle;
    }

    public function setExtendedTitle($title)
    {
        $this->extendedTitle = $title;
    }
}
