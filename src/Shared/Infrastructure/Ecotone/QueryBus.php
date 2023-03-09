<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Ecotone;

use App\Shared\Application\Query\QueryBusInterface;
use App\Shared\Domain\Query\QueryInterface;
use Ecotone\Modelling\QueryBus as EcotoneQueryBus;

final readonly class QueryBus implements QueryBusInterface
{
    public function __construct(private EcotoneQueryBus $queryBus)
    {
    }

    public function ask(QueryInterface $query): mixed
    {
        return $this->queryBus->send($query);
    }
}
