<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use DateTimeInterface;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractTimestampableMappedSuperclassEntity implements TimestampableInterface
{
    use TimestampableTrait;

    /**
     * @ORM\Column(type="datetime")
     */
    protected DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    protected DateTimeInterface $updatedAt;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
