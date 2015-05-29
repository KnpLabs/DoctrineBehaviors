<?php

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\Model\Tree;

use Knp\DoctrineBehaviors\Model\Tree\NodeInterface;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/*
 * @author     Florian Klein <florian.klein@free.fr>
 */
trait Node
{
    use NodeProperties,
        NodeMethods
    ;
}
