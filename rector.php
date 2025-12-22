<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Set\ValueObject\LevelSetList;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/utils'])
    ->withImportNames(importShortClasses: false)
    ->withParallel()

    ->withPreparedSets(deadCode: true, codeQuality: true, codingStyle: true, naming: true)
    ->withComposerBased(doctrine: true)
    ->withSets([LevelSetList::UP_TO_PHP_80])
    ->withSkip([
        RenamePropertyToMatchTypeRector::class => [__DIR__ . '/tests/ORM/'],
        RenameVariableToMatchMethodCallReturnTypeRector::class,
    ])
;
