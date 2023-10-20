<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Sluggable;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Exception\ShouldNotHappenException;
use Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait;

#[Entity]
class SluggableEntity implements SluggableInterface
{
    use SluggableTrait;

    #[Column(type: 'string')]
    private ?string $name = null;

    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'datetime')]
    private DateTimeInterface|DateTime $dateTime;

    public function __construct()
    {
        $this->dateTime = (new DateTime())->modify('-1 year');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        if ($this->name === null) {
            throw new ShouldNotHappenException();
        }

        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->dateTime;
    }

    public function setDate(DateTimeInterface $dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @return string[]
     */
    public function getSluggableFields(): array
    {
        return ['name'];
    }
}
