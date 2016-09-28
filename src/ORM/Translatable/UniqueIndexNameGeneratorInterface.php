<?php

namespace Knp\DoctrineBehaviors\ORM\Translatable;

use Doctrine\ORM\Mapping\ClassMetadata;

interface UniqueIndexNameGeneratorInterface {

    public function generate(ClassMetadata $classMetadata);

}