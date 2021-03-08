<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Tree;

use Doctrine\Common\Collections\Collection;
use Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;

trait TreeNodePropertiesTrait
{
    protected string $materializedPath = '';

    /**
     * @var Collection<TreeNodeInterface>|null
     */
    private ?Collection $childNodes = null;

    private ?TreeNodeInterface $parentNode = null;
}
