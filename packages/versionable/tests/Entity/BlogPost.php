<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Versionable\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Versionable\Behavior\VersionablePropertiesTrait;
use Knp\DoctrineBehaviors\Versionable\Contract\VersionableInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="posts")
 */
class BlogPost implements VersionableInterface
{
    use VersionablePropertiesTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var int
     */
    public $id;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    private $content;

    public function __construct(int $id, string $content)
    {
        $this->id = $id;
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function changeContent(string $content): void
    {
        $this->content = $content;
    }
}
