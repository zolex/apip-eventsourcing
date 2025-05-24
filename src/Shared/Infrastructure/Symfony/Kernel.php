<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony;

use App\Shared\Infrastructure\Symfony\DependencyInjection\ProjectionsCompilerPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
    {
        $container->import(sprintf('%s/config/{packages}/*.php', $this->getProjectDir()));
        $container->import(sprintf('%s/config/{packages}/%s/*.php', $this->getProjectDir(), $this->environment));

        $container->import(sprintf('%s/config/{services}/*.php', $this->getProjectDir()));
        $container->import(sprintf('%s/config/{services}/%s/*.php', $this->getProjectDir(), $this->environment));
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(sprintf('%s/config/{routes}/%s/*.php', $this->getProjectDir(), $this->environment));
        $routes->import(sprintf('%s/config/{routes}/*.php', $this->getProjectDir()));
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ProjectionsCompilerPass());
    }
}
