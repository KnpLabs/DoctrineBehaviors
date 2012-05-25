<?php

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\Model\Geocodable;

/**Geocodable
 *  trait.
 *
 * Should be used inside entity where you need to manipulate geographical information
 */
trait Geocodable
{
    /**
     * @ORM\Column(type="point", nullable=true)
     */
    private $location;

    /**
     * Get location.
     *
     * @return location.
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set location.
     *
     * @param location the value to set.
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }
}
