<?php

declare(strict_types=1);

use Rector\DeadCode\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector;
use Rector\SOLID\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('paths', [__DIR__ . '/src', __DIR__ . '/tests']);

    $parameters->set('sets', ['dead-code', 'code-quality', 'coding-style', 'nette-utils-code-quality']);

    $parameters->set(
        'exclude_rectors',
        [
            RemoveUnusedDoctrineEntityMethodAndPropertyRector::class,
            UseInterfaceOverImplementationInConstructorRector::class,
        ]
    );

    $parameters->set('exclude_paths', [__DIR__ . '/src/Model/Translatable/TranslatableMethodsTrait.php']);
};
