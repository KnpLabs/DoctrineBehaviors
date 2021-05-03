<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Nette\Set\NetteSetList;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/packages',
        __DIR__ . '/utils',
    ]);

    $parameters->set(Option::SETS, [
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::NAMING,
        NetteSetList::NETTE_UTILS_CODE_QUALITY,
        // PHP upgrades
        SetList::PHP_53,
        SetList::PHP_54,
        SetList::PHP_55,
        SetList::PHP_56,
        SetList::PHP_70,
        SetList::PHP_71,
        SetList::PHP_72,
        SetList::PHP_73,
    ]);
};
