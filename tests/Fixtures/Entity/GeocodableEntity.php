<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\GeocodableInterface;
use Knp\DoctrineBehaviors\Model\Geocodable\GeocodableTrait;
use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;

/**
 * @ORM\Entity(repositoryClass="Knp\DoctrineBehaviors\Tests\Fixtures\Repository\GeocodableRepository")
 */
class GeocodableEntity implements GeocodableInterface
{
    use GeocodableTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $title;

    /**
     * @param int|float $latitude
     * @param int|float $longitude
     */
    public function __construct($latitude = 0, $longitude = 0)
    {
        $point = new Point($latitude, $longitude);
        $this->setLocation($point);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
