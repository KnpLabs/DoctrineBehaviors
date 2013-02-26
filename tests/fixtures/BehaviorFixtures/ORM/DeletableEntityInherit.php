<?php

namespace BehaviorFixtures\ORM;

/**
 * @ORM\Entity
 */
class DeletableEntityInherit extends DefaultDeletableEntity
{

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * Returns object name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
