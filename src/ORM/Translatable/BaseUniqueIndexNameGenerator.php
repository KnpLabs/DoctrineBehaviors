<?php

namespace Knp\DoctrineBehaviors\ORM\Translatable;

use Doctrine\ORM\Mapping\ClassMetadata;

class BaseUniqueIndexNameGenerator implements UniqueIndexNameGeneratorInterface {

    public function generate(ClassMetadata $classMetadata) {
        return $classMetadata->getTableName() . '_unique_translation';
    }

}