<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\GeneratedValue;
use DateTimeInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;

#[MappedSuperclass]
abstract class AbstractTimestampableMappedSuperclassEntity implements TimestampableInterface
{
    use TimestampableTrait;

    /**
     * @var DateTimeInterface
     */

    #[Column(type: 'datetime')]
    protected $createdAt;

    /**
     * @var DateTimeInterface
     */

    #[Column(type: 'datetime')]
    protected $updatedAt;

    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
