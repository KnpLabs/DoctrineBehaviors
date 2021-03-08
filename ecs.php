<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ClassNotation\FinalClassFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/utils',
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector.php',
    ]);

    $parameters->set(Option::SETS, [SetList::PSR_12, SetList::SYMPLIFY, SetList::COMMON, SetList::CLEAN_CODE]);

    $parameters->set(Option::SKIP, [
        UnaryOperatorSpacesFixer::class,
        PhpUnitStrictFixer::class => [__DIR__ . '/tests/ORM/TimestampableTest.php'],
        OrderedImportsFixer::class => [
            __DIR__ . '/tests/Fixtures/Entity/AbstractTimestampableMappedSuperclassEntity.php',
        ],
        __DIR__ . '/src/Bundle/DoctrineBehaviorsBundle.php',
        __DIR__ . '/src/DoctrineBehaviorsBundle.php',
    ]);

    $services = $containerConfigurator->services();
    $services->set(FinalClassFixer::class);
    $services->set(HeaderCommentFixer::class)
        ->call('configure', [[
            'header' => '',
        ]]);

    $services->set(GeneralPhpdocAnnotationRemoveFixer::class)
        ->call('configure', [[
            'annotations' => ['author', 'package', 'license', 'link', 'abstract'],
        ]]);
};
