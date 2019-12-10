<?php

declare(strict_types=1);

namespace BehaviorFixtures\ORM;

use BehaviorFixtures\ORM\Translation\TranslatableCustomizedEntityTranslation;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;

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
