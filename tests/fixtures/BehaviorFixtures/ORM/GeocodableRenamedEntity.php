<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Geocodable\Geocodable;
use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;

/**
 * @ORM\Entity(repositoryClass="BehaviorFixtures\ORM\GeoCodableEntityRepository")
 */
class GeocodableRenamedEntity
{
    use Geocodable {
        Geocodable::getLocation as getTraitLocation;
        Geocodable::setLocation as setTraitLocation;
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

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $privateLocation;

    public function __construct($latitude = 0, $longitude = 0)
    {
        $this->setLocation(new Point($latitude, $longitude));
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

    /**
     * Get location.
     *
     * @return privateLocation.
     */
    public function getLocation()
    {        
        return $this->privateLocation;
    }

    /**
     * Set privateLocation.
     *
     * @param privateLocation the value to set.
     */
    public function setLocation($privateLocation)
    {
        $this->privateLocation = $privateLocation;
    }
}
