<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model;

/**
 * @ORM\Entity
 * @ORM\Table(name="SluggableEntity")
 */
class RussianSluggableEntity extends SluggableEntity
{
    protected function getTransliterator()
    {
        return ['\Knp\DoctrineBehaviors\Model\Sluggable\Utils', 'transliterateRussian'];
    }
} 