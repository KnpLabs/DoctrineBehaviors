<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translatable\V1;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ExtendedV1TranslatableEntity extends AbstractV1TranslatableEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    public function getId(): int
    {
        return $this->id;
    }
}
