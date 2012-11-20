<?php

namespace BehaviorFixtures\ODM;

use Knp\DoctrineBehaviors\ODM\Tree;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * @author     Florian Klein <florian.klein@free.fr>
 */
class TreeNodeDocumentRepository extends DocumentRepository
{
    use Tree\Tree;
}

