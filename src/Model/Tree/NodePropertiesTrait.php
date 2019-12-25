<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Tree;

use Doctrine\Common\Collections\Collection;
use Knp\DoctrineBehaviors\Contract\Model\Tree\NodeInterface;

trait NodePropertiesTrait
{
    /**
     * @var string
     */
    protected $materializedPath = '';

    /**
     * @var Collection|NodeInterface[]
     */
    private $childNodes;

    /**
     * @var NodeInterface|null
     */
    private $parentNode;
}
