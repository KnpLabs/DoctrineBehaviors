<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\DeadCode\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/utils']);

    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $parameters->set(Option::SETS, [
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::TYPE_DECLARATION,
        SetList::TYPE_DECLARATION_STRICT,
        SetList::NETTE_UTILS_CODE_QUALITY,
        SetList::NAMING,
        SetList::PHP_70,
        SetList::PHP_71,
        SetList::PHP_72,
        SetList::PHP_73,
        SetList::PHP_74,
    ]);

    $parameters->set(Option::SKIP, [
        RemoveUnusedDoctrineEntityMethodAndPropertyRector::class,
        __DIR__ . '/src/Model/Translatable/TranslatableMethodsTrait.php',

        ParamTypeDeclarationRector::class => [
            __DIR__ . '/src/Model/Tree/TreeNodeMethodsTrait.php'
        ],
    ]);
};
