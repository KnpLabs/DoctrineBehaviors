<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Taggable\Taggable;

/**
 * @ORM\Entity
 * @ORM\Table(name="taggable")
 */
class TaggableEntity
{

    use Taggable;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
