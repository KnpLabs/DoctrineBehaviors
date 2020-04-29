<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\DependencyInjection;

use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DependencyInjectionTest extends KernelTestCase
{
    /**
     * @dataProvider getServices
     */
    public function testServicesRegistration(string $key): void
    {
        self::bootKernel();

        $this->assertTrue(static::$container->has($key), sprintf('Service "%s" doesn\'t seem to be registered', $key));

        $service = static::$container->get($key);
        $this->assertNotNull($service, sprintf('Instance of "%s" should not be null', $key));
    }

    public function getServices(): Generator
    {
        yield ['knp_doctrine_behaviors.user_provider'];
        yield ['knp_doctrine_behaviors.locale_provider'];
        yield ['knp_doctrine_behaviors.repository.default_sluggable'];
        yield ['knp_doctrine_behaviors.event_subscriber.blameable'];
        yield ['knp_doctrine_behaviors.event_subscriber.loggable'];
        yield ['knp_doctrine_behaviors.event_subscriber.sluggable'];
        yield ['knp_doctrine_behaviors.event_subscriber.soft_deleteable'];
        yield ['knp_doctrine_behaviors.event_subscriber.timestampable'];
        yield ['knp_doctrine_behaviors.event_subscriber.translatable'];
        yield ['knp_doctrine_behaviors.event_subscriber.tree'];
        yield ['knp_doctrine_behaviors.event_subscriber.uuidable'];
    }
}
