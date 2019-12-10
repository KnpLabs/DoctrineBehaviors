<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests;

use Knp\DoctrineBehaviors\Tests\HttpKernel\DoctrineBehaviorsKernel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

abstract class AbstractEntityManagerTestCase extends AbstractKernelTestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function setUp(): void
    {
        $kernel = $this->bootKernel(DoctrineBehaviorsKernel::class);

        $this->container = $kernel->getContainer();
    }
}
