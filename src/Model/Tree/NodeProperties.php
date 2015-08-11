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

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Trait NodeProperties
 *
 * @author Florian Klein <florian.klein@free.fr>
 */
trait NodeProperties
{
    /**
     * @var ArrayCollection $childNodes the children in the tree
     */
    private $childNodes;

    /**
     * @var NodeInterface $parentNode the parent in the tree
     */
    private $parentNode;

    /**
     * @var string
     */
    protected $materializedPath = '';
}
