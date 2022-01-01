<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Nette\Set\NetteSetList;
use Rector\Php80\Rector\FunctionLike\UnionTypesRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/utils']);
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
    $parameters->set(Option::PARALLEL, true);

    $parameters->set(Option::SKIP, [
        RenamePropertyToMatchTypeRector::class => [__DIR__ . '/tests/ORM/'],

        UnionTypesRector::class => [
            // to keep BC return types
            __DIR__ . '/src/Contract/Entity',
            'src/Model/*/*Trait.php',
        ],
    ]);

    // doctrine annotations to attributes
    $containerConfigurator->import(DoctrineSetList::DOCTRINE_ORM_29);

    $containerConfigurator->import(SetList::DEAD_CODE);
    $containerConfigurator->import(SetList::CODE_QUALITY);
    $containerConfigurator->import(SetList::CODING_STYLE);
    $containerConfigurator->import(NetteSetList::NETTE_CODE_QUALITY);
    $containerConfigurator->import(SetList::NAMING);
    $containerConfigurator->import(LevelSetList::UP_TO_PHP_80);
};
