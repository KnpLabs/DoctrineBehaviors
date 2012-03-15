<?php

namespace Knp\DoctrineBehaviors\ORM\Translation;

use Knp\DoctrineBehaviors\ORM\Tree\LeafInterface;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

trait Translation
{
    protected $translatable;
}
