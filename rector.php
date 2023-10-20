<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/utils']);
    $rectorConfig->importNames();
    $rectorConfig->parallel();

    $rectorConfig->skip([
        RenamePropertyToMatchTypeRector::class => [__DIR__ . '/tests/ORM/'],
        TypedPropertyFromAssignsRector::class => [__DIR__ . '/tests/Repository/'],
    ]);

    // doctrine annotations to attributes
    $rectorConfig->sets([
        DoctrineSetList::DOCTRINE_ORM_29,
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::NAMING,
        LevelSetList::UP_TO_PHP_80,
    ]);
};
