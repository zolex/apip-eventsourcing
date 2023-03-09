<?php

declare(strict_types=1);

namespace App\Shared\Application\Query;

use App\Shared\Domain\Query\QueryInterface;

interface QueryBusInterface
{
    public function ask(QueryInterface $query): mixed;
}
