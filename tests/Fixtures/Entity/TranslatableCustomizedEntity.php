<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translation\TranslatableCustomizedEntityTranslation;

/**
 * Used to test translation classes which declare custom translatable classes.
 *
 * @ORM\Entity
 * @phpstan-implements TranslatableInterface<TranslatableCustomizedEntityTranslation>
 */
class TranslatableCustomizedEntity implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

    public static function getTranslationEntityClass(): string
    {
        return TranslatableCustomizedEntityTranslation::class;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
