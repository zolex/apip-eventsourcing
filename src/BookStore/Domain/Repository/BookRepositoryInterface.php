<?php

declare(strict_types=1);

namespace App\BookStore\Domain\Repository;

use App\BookStore\Domain\Event\BookEvent;
use App\BookStore\Domain\Model\Book;
use App\BookStore\Domain\ValueObject\Author;
use App\BookStore\Domain\ValueObject\BookId;
use App\Shared\Domain\Repository\PaginatorInterface;

interface BookRepositoryInterface
{
    public function ofId(BookId $id): ?Book;

    /** @return iterable<BookEvent> */
    public function findEvents(BookId $id): iterable;

    /** @return iterable<Book> */
    public function all(): iterable;

    /**
     * @return PaginatorInterface<Book>
     */
    public function paginator(int $page, int $itemsPerPage): PaginatorInterface;

    /** @return iterable<Book> */
    public function findByAuthor(Author $author): iterable;

    /** @return iterable<Book> */
    public function findCheapest(int $size): iterable;
}
