<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model;

/**
 * @ORM\Entity
 */
class RenamedTranslatableEntityTranslation
{
    use Model\Translatable\Translation
    {
        Model\Translatable\Translation::setTranslatable as setTraitTranslatable;
        Model\Translatable\Translation::getTranslatable as getTraitTranslatable;
        Model\Translatable\Translation::setLocale       as setTraitLocale;
        Model\Translatable\Translation::getLocale       as getTraitLocale;
        Model\Translatable\Translation::isEmpty         as isTraitEmpty;
    }

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

    public function setTranslatable()
    {
        throw new BadMethodCallException($this, 'setTranslatable');
    }

    public function getTranslatable()
    {
        throw new BadMethodCallException($this, 'getTranslatable');
    }

    public function setLocale()
    {
        throw new BadMethodCallException($this, 'setLocale');
    }

    public function getLocale()
    {
        throw new BadMethodCallException($this, 'getLocale');
    }

    public function isEmpty()
    {
        throw new BadMethodCallException($this, 'isEmpty');
    }
}
