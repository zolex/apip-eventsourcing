<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\Ecotone\Repository;

use App\BookStore\Domain\Model\Book;
use App\BookStore\Domain\ValueObject\BookId;
use Ecotone\Modelling\Attribute\RelatedAggregate;
use Ecotone\Modelling\Attribute\Repository;

interface EventSourcedBookRepository
{
    #[Repository]
    public function findBy(BookId $bookId): ?Book;

    #[Repository]
    public function getBy(BookId $bookId): Book;

    #[Repository]
    #[RelatedAggregate(Book::class)]
    public function save(BookId $bookId, int $currentVersion, array $events): void;
}
