<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Geocodable\Type;

final class Point
{
    /**
     * @var float|int
     */
    private $latitude;

    /**
     * @var float|int
     */
    private $longitude;

    /**
     * @param float|int $latitude
     * @param float|int $longitude
     */
    public function __construct($latitude, $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Returns string representation for Point in (%f,%f) format.
     */
    public function __toString(): string
    {
        return sprintf('(%F,%F)', $this->latitude, $this->longitude);
    }

    /**
     * @param mixed[] $array either hash or array of lat, long
     */
    public static function fromArray(array $array): self
    {
        if (isset($array['latitude'])) {
            return new self($array['latitude'], $array['longitude']);
        }

        return new self($array[0], $array[1]);
    }

    /**
     * @param string $string string in (%f,%f) format
     */
    public static function fromString(string $string): self
    {
        return self::fromArray(sscanf($string, '(%f,%f)'));
    }

    /**
     * @return float|int
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @return float|int
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    public function isEmpty(): bool
    {
        return empty($this->latitude) && empty($this->longitude);
    }
}
