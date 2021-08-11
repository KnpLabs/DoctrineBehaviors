<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractTimestampableMappedSuperclassEntity implements TimestampableInterface
{
    use TimestampableTrait;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTimeInterface
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTimeInterface
     */
    protected $updatedAt;

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
