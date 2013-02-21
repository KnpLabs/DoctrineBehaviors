<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model;
use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;

/**
 * @ORM\Entity(repositoryClass="BehaviorFixtures\ORM\GeocodableEntityRepository")
 */
class RenamedGeocodableEntity
{
    use Model\Geocodable\Geocodable
    {
        Model\Geocodable\Geocodable::getLocation as getTraitLocation;
        Model\Geocodable\Geocodable::setLocation as setTraitLocation;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $title;

    public function __construct($latitude = 0, $longitude = 0)
    {
        $this->setTraitLocation(new Point($latitude, $longitude));
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

    /**
     * Get title.
     *
     * @return title.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param title the value to set.
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getLocation()
    {
        throw new BadMethodCallException($this, 'getLocation');
    }

    public function setLocation()
    {
        throw new BadMethodCallException($this, 'setLocation');
    }
}
