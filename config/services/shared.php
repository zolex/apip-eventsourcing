<?php

declare(strict_types=1);

use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Query\QueryBusInterface;
use App\Shared\Infrastructure\Ecotone\CommandBus;
use App\Shared\Infrastructure\Ecotone\QueryBus;
use Doctrine\DBAL\Connection;
use Ecotone\Dbal\DbalConnection;
use Enqueue\Dbal\DbalConnectionFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\Shared\\', __DIR__.'/../../src/Shared')
        ->exclude([__DIR__.'/../../src/Shared/Infrastructure/Kernel.php']);

    $services->alias(CommandBusInterface::class, CommandBus::class);
    $services->alias(QueryBusInterface::class, QueryBus::class);

    $services->set(DbalConnectionFactory::class)
        ->factory([DbalConnection::class, 'create'])
        ->args([service(Connection::class)]);
};
