<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model;

/**
 * @ORM\Entity
 */
class RenamedTranslatableEntity
{
    use Model\Translatable\Translatable
    {
        Model\Translatable\Translatable::getTranslations                as getTraitTranslations;
        Model\Translatable\Translatable::getNewTranslations             as getTraitNewTranslations;
        Model\Translatable\Translatable::addTranslation                 as addTraitTranslation;
        Model\Translatable\Translatable::removeTranslation              as removeTraitTranslation;
        Model\Translatable\Translatable::translate                      as translateTrait;
        Model\Translatable\Translatable::doTranslate                    as doTraitTranslate;
        Model\Translatable\Translatable::mergeNewTranslations           as mergeTraitNewTranslations;
        Model\Translatable\Translatable::setCurrentLocale               as setTraitCurrentLocale;
        Model\Translatable\Translatable::getCurrentLocale               as getTraitCurrentLocale;
        Model\Translatable\Translatable::getDefaultLocale               as getTraitDefaultLocale;
        Model\Translatable\Translatable::proxyCurrentLocaleTranslation  as proxyTraitCurrentLocaleTranslation;
        Model\Translatable\Translatable::getTranslationEntityClass      as getTraitTranslationEntityClass;
        Model\Translatable\Translatable::findTranslationByLocale        as findTraitTranslationByLocale;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public function __call($method, $arguments)
    {
        return $this->proxyTraitCurrentLocaleTranslation($method, $arguments);
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

    public function getTranslations()
    {
        throw new BadMethodCallException($this, 'getTranslations');
    }

    public function getNewTranslations()
    {
        throw new BadMethodCallException($this, 'getNewTranslations');
    }

    public function addTranslation()
    {
        throw new BadMethodCallException($this, 'addTranslation');
    }

    public function removeTranslation()
    {
        throw new BadMethodCallException($this, 'removeTranslation');
    }

    public function translate()
    {
        throw new BadMethodCallException($this, 'translate');
    }

    public function doTranslate()
    {
        throw new BadMethodCallException($this, 'doTranslate');
    }

    public function mergeNewTranslations()
    {
        throw new BadMethodCallException($this, 'mergeNewTranslations');
    }

    public function setCurrentLocale()
    {
        throw new BadMethodCallException($this, 'setCurrentLocale');
    }

    public function getCurrentLocale()
    {
        throw new BadMethodCallException($this, 'getCurrentLocale');
    }

    public function getDefaultLocale()
    {
        throw new BadMethodCallException($this, 'getDefaultLocale');
    }

    public function proxyCurrentLocaleTranslation()
    {
        throw new BadMethodCallException($this, 'proxyCurrentLocaleTranslation');
    }

    public function getTranslationEntityClass()
    {
        throw new BadMethodCallException($this, 'getTranslationEntityClass');
    }

    public function findTranslationByLocale()
    {
        throw new BadMethodCallException($this, 'findTranslationByLocale');
    }
}
