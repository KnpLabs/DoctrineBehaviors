<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\HttpKernel;

use Knp\DoctrineBehaviors\Bundle\DoctrineBehaviorsBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

final class DoctrineBehaviorsKernel extends Kernel
{
    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        return [new DoctrineBehaviorsBundle()];
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/doctrine_behaviors_test';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/doctrine_behaviors_test_log';
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
    }
}
