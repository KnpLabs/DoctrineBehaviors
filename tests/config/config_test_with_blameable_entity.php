<?php

declare(strict_types=1);

use Knp\DoctrineBehaviors\Contract\Provider\UserProviderInterface;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\UserEntity;
use Knp\DoctrineBehaviors\Tests\Provider\EntityUserProvider;
use Knp\DoctrineBehaviors\Tests\Utils\Doctrine\DebugMiddleware;
use Knp\DoctrineBehaviors\Tests\Utils\Doctrine\DebugStack;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('doctrine_behaviors_blameable_user_entity', UserEntity::class);

    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->set(EntityUserProvider::class);
    $services->alias(UserProviderInterface::class, EntityUserProvider::class);

    $services->set(DebugStack::class)
        ->public();
    $services->set(DebugMiddleware::class)
        ->args([service(DebugStack::class)])
        ->tag('doctrine.middleware')
    ;
};
