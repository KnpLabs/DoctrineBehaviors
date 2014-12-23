<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Taggable\Tag;
use Knp\DoctrineBehaviors\Model\Taggable\TagInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="taggable_tag")
 */
class TaggableEntityTag implements TagInterface
{

    use Tag;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    private $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
