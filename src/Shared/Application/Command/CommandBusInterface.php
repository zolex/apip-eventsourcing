<?php

declare(strict_types=1);

namespace App\Shared\Application\Command;

use App\Shared\Domain\Command\CommandInterface;

interface CommandBusInterface
{
    public function dispatch(CommandInterface $command): mixed;
}
