<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Sluggable\Sluggable;

/**
 * @ORM\Entity
 */
class RenamedSluggableEntity
{
    use Sluggable
    {
        Sluggable::getSlugDelimiter             as getTraitSlugDelimiter;
        Sluggable::getRegenerateSlugOnUpdate    as getTraitRegenerateSlugOnUpdate;
        Sluggable::getSlug                      as getTraitSlug;
        Sluggable::generateSlug                 as generateTraitSlug;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date;

    public function __construct()
    {
        $this->date = (new \DateTime)->modify('-1 year');
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

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    protected function getSluggableFields()
    {
        return [ 'name' ];
    }

    public function getSlugDelimiter()
    {
        throw new BadMethodCallException($this, 'getSlugDelimiter');
    }

    public function getRegenerateSlugOnUpdate()
    {
        throw new BadMethodCallException($this, 'getRegenerateSlugOnUpdate');
    }

    public function getSlug()
    {
        throw new BadMethodCallException($this, 'getSlug');
    }

    public function generateSlug()
    {
        throw new BadMethodCallException($this, 'generateSlug');
    }
}
