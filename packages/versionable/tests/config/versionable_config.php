<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\Amnesia\ValueObject\Symfony\Extension\Doctrine\Mapping;
use Symplify\Amnesia\ValueObject\Symfony\Extension\Doctrine\ORM;
use Symplify\Amnesia\ValueObject\Symfony\Extension\DoctrineExtension;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension(DoctrineExtension::NAME, [
        DoctrineExtension::ORM => [
            ORM::MAPPINGS => [
                [
                    'name' => 'DoctrineBehaviorsVersionable',
                    Mapping::TYPE => 'annotation',
                    Mapping::PREFIX => 'Knp\DoctrineBehaviors\Versionable\Entity\\',
                    Mapping::DIR => __DIR__ . '/../../src/Entity',
                    Mapping::IS_BUNDLE => false,
                ],
                [
                    'name' => 'DoctrineBehaviorsVersionableTest',
                    Mapping::TYPE => 'annotation',
                    Mapping::PREFIX => 'Knp\DoctrineBehaviors\Versionable\Tests\Entity\\',
                    Mapping::DIR => __DIR__ . '/../../tests/Entity',
                ],
            ],
        ],
    ]);
};
