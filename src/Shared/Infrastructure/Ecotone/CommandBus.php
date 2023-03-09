<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Ecotone;

use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Domain\Command\CommandInterface;
use Ecotone\Modelling\CommandBus as EcotoneCommandBus;

final readonly class CommandBus implements CommandBusInterface
{
    public function __construct(private EcotoneCommandBus $commandBus)
    {
    }

    public function dispatch(CommandInterface $command): mixed
    {
        return $this->commandBus->send($command);
    }
}
