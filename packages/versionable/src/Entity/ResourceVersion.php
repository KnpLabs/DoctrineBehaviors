<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Versionable\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(name="snapshot_date", type="datetime")
     * @var DateTimeInterface
     */
    private $snapshotDate;

    public function __construct(string $resourceName, int $resourceId, array $versionedData, int $version)
    {
        $this->resourceName = $resourceName;
        $this->resourceId = $resourceId;
        $this->versionedData = $versionedData;
        $this->version = $version;
        $this->snapshotDate = new \DateTime('now');
    }

    public function getId()
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
        return $this->versionedData[$name] ?? null;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getSnapshotDate(): DateTimeInterface
    {
        return $this->snapshotDate;
    }
}
