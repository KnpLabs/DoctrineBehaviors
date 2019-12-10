<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\Translation\TranslatableCustomizedEntityTranslation;

/**
 * @ORM\Entity
 * Used to test translation classes which declare custom translatable classes.
 */
class TranslatableCustomizedEntity
{
    use Translatable;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

    public static function getTranslationEntityClass()
    {
        return TranslatableCustomizedEntityTranslation::class;
    }

    /**
     * Returns object id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
