<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\ORM as OrmBehaviors;

/**
 * @ORM\Entity
 */
class TranslatableEntity
{
    use OrmBehaviors\Translatable\Translatable;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

    /**
     * Returns object id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
