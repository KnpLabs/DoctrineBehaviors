<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\LoggableInterface;
use Knp\DoctrineBehaviors\Model\Loggable\LoggableTrait;

/**
 * @ORM\Entity
 */
class LoggableEntity implements LoggableInterface
{
    use LoggableTrait;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $title;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @var string[]|null
     */
    private $roles;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var DateTimeInterface|null
     */
    private $date;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function setRoles(?array $roles = null): void
    {
        $this->roles = $roles;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date): void
    {
        $this->date = $date;
    }
}
