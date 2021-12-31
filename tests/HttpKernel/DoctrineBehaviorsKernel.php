<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\HttpKernel;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\DoctrineBundle\Twig\DoctrineExtension;
use Knp\DoctrineBehaviors\DoctrineBehaviorsBundle;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel;

final class DoctrineBehaviorsKernel extends AbstractSymplifyKernel
{
    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        return [new DoctrineBehaviorsBundle(), new DoctrineBundle(), new FrameworkBundle()];
    }

    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs(array $configFiles): ContainerInterface
    {
        $configFiles[] = __DIR__ . '/../../config/services.php';
        $configFiles[] = __DIR__ . '/../config/config_test.php';

        $extensions[] = new \Doctrine\Bundle\DoctrineBundle\DependencyInjection\DoctrineExtension();

        $compilerPasses = [];

        return $this->create($extensions, $compilerPasses, $configFiles);
    }
}
