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

    public function __construct($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get id.
     *
     * @return id.
     */
    public function getId()
    {
        return $this->id;
    }

    public function getLocale()
    {
        return $this->locale;
    }
}
