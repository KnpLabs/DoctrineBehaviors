<?php

namespace Knp\DoctrineBehaviors\ORM\Translatable;


interface UniqueIndexNameGeneratorInterface {

    public function generate( $columnNames, $prefix='', $maxSize=30 );

}