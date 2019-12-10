<?php

declare(strict_types=1);

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\ORM\Tree\Tree;

class TreeNodeEntityRepository extends EntityRepository
{
    use Tree;
}
