<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translatable;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ExtendedTranslatableEntityTranslation extends AbstractTranslatableEntityTranslation
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $extendedTitle;

    public function getExtendedTitle(): string
    {
        return $this->extendedTitle;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setExtendedTitle(string $title): void
    {
        $this->extendedTitle = $title;
    }
}
