<?php

namespace Knp\DoctrineBehaviors\ORM\Translatable;

use Doctrine\DBAL\Schema\AbstractAsset;

class BaseUniqueIndexNameGenerator extends AbstractAsset implements UniqueIndexNameGeneratorInterface
{
    public function generate($columnNames, $prefix = '', $maxSize = 30)
    {
        return $this->_generateIdentifierName($columnNames, $prefix, $maxSize);
    }
}
