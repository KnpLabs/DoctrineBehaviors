<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(SetList::PSR_12);
    $containerConfigurator->import(SetList::SYMPLIFY);
    $containerConfigurator->import(SetList::COMMON);
    $containerConfigurator->import(SetList::CLEAN_CODE);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/utils',
        __DIR__ . '/ecs.php',
    ]);

    $parameters->set(Option::SKIP, [
        UnaryOperatorSpacesFixer::class,
        PhpUnitStrictFixer::class => [__DIR__ . '/tests/ORM/Timestampable/TimestampableTest.php'],
        OrderedImportsFixer::class => [
            __DIR__ . '/tests/Fixtures/Entity/AbstractTimestampableMappedSuperclassEntity.php',
        ],
        __DIR__ . '/src/Bundle/DoctrineBehaviorsBundle.php',
        __DIR__ . '/src/DoctrineBehaviorsBundle.php',
    ]);

    $services = $containerConfigurator->services();

    $services->set(HeaderCommentFixer::class)
        ->call('configure', [[
            'header' => '',
        ]]);

    $services->set(GeneralPhpdocAnnotationRemoveFixer::class)
        ->call('configure', [[
            'annotations' => ['author', 'package', 'license', 'link', 'abstract'],
        ]]);
};
