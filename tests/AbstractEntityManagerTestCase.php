<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests;

use Knp\DoctrineBehaviors\Tests\HttpKernel\DoctrineBehaviorsKernel;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

abstract class AbstractEntityManagerTestCase extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        $this->bootKernel(DoctrineBehaviorsKernel::class);
    }
}
