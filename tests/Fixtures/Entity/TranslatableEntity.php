<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

/**
 * @ORM\Entity
 * @method string|null getTitle()
 * @method void setTitle(string $title)
 */
class TranslatableEntity implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
