<?php

declare(strict_types=1);

use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use Knp\DoctrineBehaviors\Contract\Provider\UserProviderInterface;
use Knp\DoctrineBehaviors\EventSubscriber\LoggableEventSubscriber;
use Knp\DoctrineBehaviors\Tests\DatabaseLoader;
use Knp\DoctrineBehaviors\Tests\Provider\TestLocaleProvider;
use Knp\DoctrineBehaviors\Tests\Provider\TestUserProvider;
use Psr\Log\Test\TestLogger;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\Security\Core\Security;
use function Symplify\Amnesia\Functions\env;
use Symplify\Amnesia\ValueObject\Symfony\Extension\Doctrine\DBAL;
use Symplify\Amnesia\ValueObject\Symfony\Extension\Doctrine\Mapping;
use Symplify\Amnesia\ValueObject\Symfony\Extension\Doctrine\ORM;
use Symplify\Amnesia\ValueObject\Symfony\Extension\DoctrineExtension;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('env(DB_ENGINE)', 'pdo_sqlite');
    $parameters->set('env(DB_HOST)', 'localhost');
    $parameters->set('env(DB_NAME)', 'orm_behaviors_test');
    $parameters->set('env(DB_USER)', 'root');
    $parameters->set('env(DB_PASSWD)', '');
    $parameters->set('env(DB_MEMORY)', 'true');
    $parameters->set('kernel.secret', 'for_framework_bundle');
    $parameters->set('locale', 'en');

    // framework bundle
    $parameters->set('kernel.debug', true);
    $parameters->set('kernel.bundles', []);

    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->set(Security::class)
        ->arg('$container', service('service_container'));

    $services->set(TestLogger::class);

    $services->set(TestLocaleProvider::class);
    $services->alias(LocaleProviderInterface::class, TestLocaleProvider::class);

    $services->set(TestUserProvider::class);
    $services->alias(UserProviderInterface::class, TestUserProvider::class);

    $services->set(DatabaseLoader::class);

    $services->set(LoggableEventSubscriber::class)
        ->arg('$logger', service(TestLogger::class));

    $containerConfigurator->extension(DoctrineExtension::NAME, [
        DoctrineExtension::DBAL => [
            DBAL::DBNAME => env('DB_NAME'),
            DBAL::HOST => env('DB_HOST'),
            DBAL::USER => env('DB_USER'),
            DBAL::PASSWORD => env('DB_PASSWD'),
            DBAL::DRIVER => env('DB_ENGINE'),
            DBAL::MEMORY => (bool) env('DB_MEMORY'),
        ],

        DoctrineExtension::ORM => [
            ORM::AUTO_MAPPING => true,
            ORM::MAPPINGS => [
                [
                    Mapping::NAME => 'DoctrineBehaviors',
                    Mapping::TYPE => 'attribute',
                    Mapping::PREFIX => 'Knp\DoctrineBehaviors\Tests\Fixtures\Entity\\',
                    Mapping::DIR => __DIR__ . '/../../tests/Fixtures/Entity',
                    Mapping::IS_BUNDLE => false,
                ],
            ],
        ],
    ]);
};
