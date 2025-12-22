<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class DoctrineBehaviorsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $phpFileLoader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../../config'));
        $phpFileLoader->load('services.php');
    }
}
