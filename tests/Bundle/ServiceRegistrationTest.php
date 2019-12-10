<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Bundle;

use Doctrine\ORM\EntityManagerInterface;
use Knp\DoctrineBehaviors\ORM\Sluggable\SluggableSubscriber;
use Knp\DoctrineBehaviors\Tests\HttpKernel\DoctrineBehaviorsKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ServiceRegistrationTest extends KernelTestCase
{
    protected function setUp(): void
    {
        $_ENV['KERNEL_CLASS'] = DoctrineBehaviorsKernel::class;
        self::bootKernel();
    }

    public function test(): void
    {
        $sluggableSubscriber = static::$container->get(SluggableSubscriber::class);
        $this->assertInstanceOf(SluggableSubscriber::class, $sluggableSubscriber);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::$container->get('doctrine.orm.entity_manager');
        dump($entityManager);
    }
}
