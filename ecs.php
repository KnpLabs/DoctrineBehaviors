<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ClassNotation\FinalClassFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use SlevomatCodingStandard\Sniffs\Classes\UnusedPrivateElementsSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\Configuration\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector-ci.php',
    ]);

    $parameters->set(Option::SETS, ['psr12', 'php70', 'php71', 'symplify', 'common', 'clean-code']);

    $parameters->set(Option::SKIP, [
        UnaryOperatorSpacesFixer::class => null,
        PropertyTypeHintSniff::class => null,
        PhpUnitStrictFixer::class => ['tests/ORM/TimestampableTest.php'],
        UnusedPrivateElementsSniff::class => ['tests/Fixtures/Entity/SluggableWithoutRegenerateEntity.php'],
    ]);

    $parameters->set(Option::EXCLUDE_PATHS, [
        __DIR__ . '/src/Bundle/DoctrineBehaviorsBundle.php',
        __DIR__ . '/src/DoctrineBehaviorsBundle.php',
    ]);

    $services = $containerConfigurator->services();
    $services->set(FinalClassFixer::class);
    $services->set(HeaderCommentFixer::class)
        ->call('configure', [['header' => '']]);

    $services->set(GeneralPhpdocAnnotationRemoveFixer::class)
        ->call('configure', [[
            'annotations' => ['author', 'package', 'license', 'link', 'abstract'],
        ]]);

    $services->set(PropertyTypeHintSniff::class);
};
