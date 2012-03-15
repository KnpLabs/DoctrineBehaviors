<?php

namespace Knp\DoctrineBehaviors\ORM\Translatable;

use Doctrine\Common\Collections\ArrayCollection;

trait Translation
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $locale;

    /**
     * Will be mapped to translatable entity
     * by TranslatableListener
     */
    protected $translatable;

    /**
     * Get id.
     *
     * @return id.
     */
    public function getId()
    {
        return $this->id;
    }

    public function getTranslatable()
    {
        return $this->translatable;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function setTranslatable($translatable)
    {
        $this->translatable = $translatable;
    }
}
