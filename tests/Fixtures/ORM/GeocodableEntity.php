<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Geocodable\Geocodable;
use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;

/**
 * @ORM\Entity(repositoryClass="Knp\DoctrineBehaviors\Tests\Fixtures\ORM\GeocodableEntityRepository")
 */
class GeocodableEntity
{
    use Geocodable;

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

    public function __construct($latitude = 0, $longitude = 0)
    {
        $this->setLocation(new Point($latitude, $longitude));
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
