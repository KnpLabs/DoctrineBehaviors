<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Php80\Rector\FunctionLike\UnionTypesRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/utils']);
    $rectorConfig->importNames();
    $rectorConfig->parallel();

    $rectorConfig->skip([
        RenamePropertyToMatchTypeRector::class => [__DIR__ . '/tests/ORM/'],

        UnionTypesRector::class => [
            // to keep BC return types
            __DIR__ . '/src/Contract/Entity',
            'src/Model/*/*Trait.php',
        ],
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
