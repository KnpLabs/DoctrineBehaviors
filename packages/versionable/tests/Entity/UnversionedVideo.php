<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Versionable\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Versionable\Contract\VersionableInterface;

/**
 * @ORM\Entity
 * @ORM\Table
 */
class UnversionedVideo implements VersionableInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var int
     */
    public $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $title;

    public function __construct(int $id, string $title)
    {
        $this->id = $id;
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function changeTitle(string $title): void
    {
        $this->title = $title;
    }
}
