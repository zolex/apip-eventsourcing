<?php

declare(strict_types=1);

namespace App\BookStore\Application\Query;

use App\BookStore\Domain\Query\FindBooksQuery;
use App\BookStore\Domain\Repository\BookRepositoryInterface;
use App\Shared\Application\Query\QueryHandlerInterface;
use Ecotone\Modelling\Attribute\QueryHandler;

final readonly class FindBooksQueryHandler implements QueryHandlerInterface
{
    public function __construct(private BookRepositoryInterface $bookRepository)
    {
    }

    #[QueryHandler]
    public function __invoke(FindBooksQuery $query): iterable
    {
        if (null !== $query->author) {
            return $this->bookRepository->findByAuthor($query->author);
        }

        if (null !== $query->page && null !== $query->itemsPerPage) {
            return $this->bookRepository->paginator($query->page, $query->itemsPerPage);
        }

        return $this->bookRepository->all();
    }
}
