<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableMethodsTrait;

/**
 * @ORM\Entity
 */
class TimestampableCustomFieldsEntity implements TimestampableInterface
{
    use TimestampableMethodsTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $title;

    /**
     * @var DateTimeInterface|null
     */
    private $serverCreatedAt;

    /**
     * @var DateTimeInterface|null
     */
    private $serverUpdatedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getServerCreatedAt(): ?DateTimeInterface
    {
        return $this->serverCreatedAt;
    }

    public function setServerCreatedAt(DateTimeInterface $serverCreatedAt): void
    {
        $this->serverCreatedAt = $serverCreatedAt;
    }

    public function getServerUpdatedAt(): ?DateTimeInterface
    {
        return $this->serverUpdatedAt;
    }

    public function setServerUpdatedAt(DateTimeInterface $serverUpdatedAt): void
    {
        $this->serverUpdatedAt = $serverUpdatedAt;
    }

    /**
     * @return string[]
     */
    public function getCreatedAtProperties(): array
    {
        return ['serverCreatedAt'];
    }

    /**
     * @return string[]
     */
    public function getUpdatedAtProperties(): array
    {
        return ['serverUpdatedAt'];
    }
}
