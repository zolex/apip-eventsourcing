<?php

declare(strict_types=1);

namespace App\BookStore\Application\Query;

use App\BookStore\Domain\Query\FindCheapestBooksQuery;
use App\BookStore\Domain\Repository\BookRepositoryInterface;
use App\Shared\Application\Query\QueryHandlerInterface;
use Ecotone\Modelling\Attribute\QueryHandler;

final readonly class FindCheapestBooksQueryHandler implements QueryHandlerInterface
{
    public function __construct(private BookRepositoryInterface $bookRepository)
    {
    }

    #[QueryHandler]
    public function __invoke(FindCheapestBooksQuery $query): iterable
    {
        return $this->bookRepository->findCheapest($query->size);
    }
}
