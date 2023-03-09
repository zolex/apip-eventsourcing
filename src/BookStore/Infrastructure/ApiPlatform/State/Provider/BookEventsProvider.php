<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\ArrayPaginator;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\ProviderInterface;
use App\BookStore\Domain\Event\BookEvent;
use App\BookStore\Domain\Query\FindBookEventsQuery;
use App\BookStore\Domain\ValueObject\BookId;
use App\Shared\Application\Query\QueryBusInterface;

/**
 * @implements ProviderInterface<BookEvent>
 */
final readonly class BookEventsProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private Pagination $pagination,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PaginatorInterface|array
    {
        /** @var string $id */
        $id = $uriVariables['id'];

        /** @var \Generator<BookEvent> $bookEvents */
        $bookEvents = $this->queryBus->ask(new FindBookEventsQuery(new BookId($id)));
        $bookEvents = iterator_to_array($bookEvents);

        if ($this->pagination->isEnabled($operation, $context)) {
            $offset = $this->pagination->getPage($context);
            $limit = $this->pagination->getLimit($operation, $context);

            return new ArrayPaginator($bookEvents, ($offset - 1) * $limit, $limit);
        }

        return $bookEvents;
    }
}
