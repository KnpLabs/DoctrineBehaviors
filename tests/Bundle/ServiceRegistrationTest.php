<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Bundle;

use Knp\DoctrineBehaviors\ORM\Sluggable\SluggableSubscriber;
use Knp\DoctrineBehaviors\Tests\HttpKernel\DoctrineBehaviorsKernel;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class ServiceRegistrationTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        $this->bootKernel(DoctrineBehaviorsKernel::class);
    }

    public function test(): void
    {
        $sluggableSubscriber = self::$container->get(SluggableSubscriber::class);
        $this->assertInstanceOf(SluggableSubscriber::class, $sluggableSubscriber);
    }
}
