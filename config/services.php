<?php

declare(strict_types=1);

use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use Knp\DoctrineBehaviors\Contract\Provider\UserProviderInterface;
use Knp\DoctrineBehaviors\EventSubscriber\BlameableEventSubscriber;
use Knp\DoctrineBehaviors\EventSubscriber\LoggableEventSubscriber;
use Knp\DoctrineBehaviors\EventSubscriber\SluggableEventSubscriber;
use Knp\DoctrineBehaviors\EventSubscriber\SoftDeletableEventSubscriber;
use Knp\DoctrineBehaviors\EventSubscriber\TimestampableEventSubscriber;
use Knp\DoctrineBehaviors\EventSubscriber\TranslatableEventSubscriber;
use Knp\DoctrineBehaviors\EventSubscriber\TreeEventSubscriber;
use Knp\DoctrineBehaviors\EventSubscriber\UuidableEventSubscriber;
use Knp\DoctrineBehaviors\Provider\LocaleProvider;
use Knp\DoctrineBehaviors\Provider\UserProvider;
use Knp\DoctrineBehaviors\Repository\DefaultSluggableRepository;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\Security\Core\Security;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('doctrine_behaviors_translatable_fetch_mode', 'LAZY');
    $parameters->set('doctrine_behaviors_translation_fetch_mode', 'LAZY');
    $parameters->set('doctrine_behaviors_blameable_user_entity', null);
    $parameters->set('doctrine_behaviors_timestampable_date_field_type', 'datetime');

    $services = $containerConfigurator->services();

    $services
        ->set(BlameableEventSubscriber::class, BlameableEventSubscriber::class)
        ->public()
        ->tag('doctrine.event_subscriber')
        ->args([
            service(UserProviderInterface::class),
            service('doctrine.orm.default_entity_manager'),
            param('doctrine_behaviors_blameable_user_entity'),
        ])

        ->set(LoggableEventSubscriber::class, LoggableEventSubscriber::class)
        ->public()
        ->tag('doctrine.event_subscriber')
        ->args([service('logger')])

        ->set(SluggableEventSubscriber::class, SluggableEventSubscriber::class)
        ->public()
        ->tag('doctrine.event_subscriber')
        ->args([service('doctrine.orm.default_entity_manager'), service(DefaultSluggableRepository::class)])

        ->set(SoftDeletableEventSubscriber::class, SoftDeletableEventSubscriber::class)
        ->public()
        ->tag('doctrine.event_subscriber')

        ->set(TimestampableEventSubscriber::class, TimestampableEventSubscriber::class)
        ->public()
        ->tag('doctrine.event_subscriber')
        ->args([param('doctrine_behaviors_timestampable_date_field_type')])

        ->set(TranslatableEventSubscriber::class, TranslatableEventSubscriber::class)
        ->public()
        ->tag('doctrine.event_subscriber')
        ->args([
            service(LocaleProviderInterface::class),
            param('doctrine_behaviors_translatable_fetch_mode'),
            param('doctrine_behaviors_translation_fetch_mode'),
        ])

        ->set(TreeEventSubscriber::class, TreeEventSubscriber::class)
        ->public()
        ->tag('doctrine.event_subscriber')

        ->set(UuidableEventSubscriber::class, UuidableEventSubscriber::class)
        ->public()
        ->tag('doctrine.event_subscriber')

        ->set(LocaleProvider::class, LocaleProvider::class)
        ->public()
        ->args([service('request_stack'), service('parameter_bag'), service('translator') ->nullOnInvalid()])

        ->alias(LocaleProviderInterface::class, LocaleProvider::class)

        ->set(UserProvider::class, UserProvider::class)
        ->public()
        ->args([service(Security::class), param('doctrine_behaviors_blameable_user_entity')])

        ->alias(UserProviderInterface::class, UserProvider::class)

        ->set(DefaultSluggableRepository::class, DefaultSluggableRepository::class)
        ->public()
        ->args([service('doctrine.orm.default_entity_manager')]);
};
