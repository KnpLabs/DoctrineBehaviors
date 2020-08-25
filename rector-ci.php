<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\DeadCode\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector;
use Rector\Set\ValueObject\SetList;
use Rector\SOLID\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/utils']);

    $parameters->set(Option::SETS, [
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::NETTE_UTILS_CODE_QUALITY,
        SetList::NAMING,
    ]);

    $parameters->set(Option::EXCLUDE_RECTORS, [
        RemoveUnusedDoctrineEntityMethodAndPropertyRector::class,
        UseInterfaceOverImplementationInConstructorRector::class,
    ]);

    $parameters->set(Option::EXCLUDE_PATHS, [__DIR__ . '/src/Model/Translatable/TranslatableMethodsTrait.php']);
};
