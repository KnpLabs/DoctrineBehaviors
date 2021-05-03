<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Versionable\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Versionable\Exception\MissingColumnException;

/**
 * @ORM\Entity
 * @ORM\Table(name="versionable_resources")
 */
class ResourceVersion
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="resource_name", type="string")
     * @var string
     */
    private $resourceName;

    /**
     * @ORM\Column(name="resource_id", type="string")
     * @var string|int
     */
    private $resourceId;

    /**
     * @ORM\Column(name="versioned_data", type="array")
     * @var array<string, mixed>
     */
    private $versionedData = [];

    /**
     * @ORM\Column(name="snapshot_version_id", type="integer")
     * @var int
     */
    private $version;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     * @var DateTimeInterface
     */
    private $createdAt;

    public function __construct(string $resourceName, int $resourceId, array $versionedData, int $version)
    {
        $this->resourceName = $resourceName;
        $this->resourceId = $resourceId;
        $this->versionedData = $versionedData;
        $this->version = $version;
        $this->createdAt = new \DateTime('now');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    /**
     * @return int|string
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * @return array<string, mixed>
     */
    public function getVersionedData(): array
    {
        return $this->versionedData;
    }

    /**
     * @return mixed
     */
    public function getVersionedColumn(string $name)
    {
        // throw exception on missing column
        if (! array_key_exists($name, $this->versionedData)) {
            $columnNames = array_keys($this->versionedData);
            $errorMessage = sprintf(
                'Column "%s" is not available. Pick one from "%s"',
                $name,
                implode('", "', $columnNames)
            );

            throw new MissingColumnException($errorMessage);
        }

        return $this->versionedData[$name];
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }
}
