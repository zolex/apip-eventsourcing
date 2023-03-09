<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\Ecotone\Repository;

use App\BookStore\Domain\Event\BookEvent;
use App\BookStore\Domain\Exception\MissingBookException;
use App\BookStore\Domain\Model\Book;
use App\BookStore\Domain\Repository\BookRepositoryInterface;
use App\BookStore\Domain\ValueObject\Author;
use App\BookStore\Domain\ValueObject\BookId;
use App\BookStore\Infrastructure\Ecotone\Projection\BookIdsGateway;
use App\BookStore\Infrastructure\Ecotone\Projection\BookPriceGateway;
use App\BookStore\Infrastructure\Ecotone\Projection\BooksByAuthorGateway;
use App\Shared\Domain\Repository\CallbackPaginator;
use App\Shared\Domain\Repository\PaginatorInterface;
use Ecotone\EventSourcing\EventStore;
use Ecotone\EventSourcing\Prooph\LazyProophEventStore;
use Prooph\EventStore\Metadata\MetadataMatcher;
use Prooph\EventStore\Metadata\Operator;
use Webmozart\Assert\Assert;

final readonly class BookRepository implements BookRepositoryInterface
{
    public function __construct(
        private EventSourcedBookRepository $eventSourcedRepository,
        private EventStore $eventStore,
        private BookIdsGateway $bookIdsGateway,
        private BookPriceGateway $bookPriceGateway,
        private BooksByAuthorGateway $booksByAuthorGateway,
    ) {
    }

    public function ofId(BookId $id): ?Book
    {
        if (!$eventSourcedBook = $this->eventSourcedRepository->findBy($id)) {
            return null;
        }

        if ($eventSourcedBook->deleted()) {
            return null;
        }

        return $eventSourcedBook;
    }

    public function findEvents(BookId $id): iterable
    {
        $matcher = (new MetadataMatcher())
            ->withMetadataMatch(LazyProophEventStore::AGGREGATE_ID, Operator::EQUALS(), (string) $id);

        $events = $this->eventStore->load(Book::class, 1, null, $matcher);

        foreach ($events as $event) {
            $bookEvent = $event->getPayload();
            Assert::isInstanceOf($bookEvent, BookEvent::class);

            yield $bookEvent;
        }
    }

    public function all(): iterable
    {
        foreach ($this->bookIdsGateway->getBookIds() as $bookId) {
            if ($book = $this->ofId(new BookId($bookId))) {
                yield $book;
            }
        }
    }

    public function paginator(int $page, int $itemsPerPage): PaginatorInterface
    {
        $firstResult = ($page - 1) * $itemsPerPage;
        $maxResults = $itemsPerPage;

        return new CallbackPaginator(
            array_map(static fn (string $bookId) => new BookId($bookId), $this->bookIdsGateway->getBookIds()),
            $firstResult,
            $maxResults,
            fn (BookId $bookId) => $this->ofId($bookId) ?? throw new MissingBookException($bookId),
        );
    }

    public function findByAuthor(Author $author): iterable
    {
        $byAuthorBookIds = $this->booksByAuthorGateway->getByAuthorBookIds();

        foreach ($byAuthorBookIds[$author->value] ?? [] as $bookId) {
            if ($book = $this->ofId(new BookId($bookId))) {
                yield $book;
            }
        }
    }

    public function findCheapest(int $size): iterable
    {
        $bookPriceList = $this->bookPriceGateway->getBookPriceList();

        asort($bookPriceList);

        return new CallbackPaginator(
            array_map(static fn (string $bookId) => new BookId($bookId), array_keys($bookPriceList)),
            0,
            $size,
            fn (BookId $bookId) => $this->ofId($bookId) ?? throw new MissingBookException($bookId),
        );
    }
}
