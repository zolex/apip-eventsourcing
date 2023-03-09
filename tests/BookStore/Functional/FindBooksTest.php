<?php

declare(strict_types=1);

namespace App\Tests\BookStore\Functional;

use App\BookStore\Domain\Model\Book;
use App\BookStore\Domain\Query\FindBooksQuery;
use App\BookStore\Domain\Repository\BookRepositoryInterface;
use App\BookStore\Domain\ValueObject\Author;
use App\BookStore\Domain\ValueObject\BookId;
use App\BookStore\Infrastructure\Ecotone\Repository\EventSourcedBookRepository;
use App\Shared\Application\Query\QueryBusInterface;
use App\Tests\BookStore\DummyFactory\DummyBookFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class FindBooksTest extends KernelTestCase
{
    public function testFindBooks(): void
    {
        /** @var EventSourcedBookRepository $eventSourcedBookRepository */
        $eventSourcedBookRepository = static::getContainer()->get(EventSourcedBookRepository::class);

        /** @var QueryBusInterface $queryBus */
        $queryBus = static::getContainer()->get(QueryBusInterface::class);

        $initialBookEvents = [
            $bookId = (string) new BookId() => DummyBookFactory::createBookWasCreatedEvent(id: new BookId($bookId)),
            $bookId = (string) new BookId() => DummyBookFactory::createBookWasCreatedEvent(id: new BookId($bookId)),
            $bookId = (string) new BookId() => DummyBookFactory::createBookWasCreatedEvent(id: new BookId($bookId)),
            $bookId = (string) new BookId() => DummyBookFactory::createBookWasCreatedEvent(id: new BookId($bookId)),
            $bookId = (string) new BookId() => DummyBookFactory::createBookWasCreatedEvent(id: new BookId($bookId)),
        ];

        foreach ($initialBookEvents as $bookId => $bookEvent) {
            $eventSourcedBookRepository->save(new BookId($bookId), 0, [$bookEvent]);
        }

        /** @var Book[] $books */
        $books = iterator_to_array($queryBus->ask(new FindBooksQuery()));

        static::assertCount(count($initialBookEvents), $books);
        foreach ($books as $book) {
            static::assertArrayHasKey((string) $book->id(), $initialBookEvents);
        }
    }

    public function testFilterBooksByAuthor(): void
    {
        /** @var EventSourcedBookRepository $eventSourcedBookRepository */
        $eventSourcedBookRepository = static::getContainer()->get(EventSourcedBookRepository::class);

        /** @var BookRepositoryInterface $bookRepository */
        $bookRepository = static::getContainer()->get(BookRepositoryInterface::class);

        /** @var QueryBusInterface $queryBus */
        $queryBus = static::getContainer()->get(QueryBusInterface::class);

        $eventSourcedBookRepository->save($bookId = new BookId(), 0, [DummyBookFactory::createBookWasCreatedEvent(
            id: $bookId,
            author: 'authorOne',
        )]);
        $eventSourcedBookRepository->save($bookId = new BookId(), 0, [DummyBookFactory::createBookWasCreatedEvent(
            id: $bookId,
            author: 'authorOne',
        )]);
        $eventSourcedBookRepository->save($bookId = new BookId(), 0, [DummyBookFactory::createBookWasCreatedEvent(
            id: $bookId,
            author: 'authorTwo',
        )]);

        static::assertCount(3, iterator_to_array($bookRepository->all()));

        /** @var Book[] $books */
        $books = iterator_to_array($queryBus->ask(new FindBooksQuery(author: new Author('authorOne'))));

        static::assertCount(2, $books);
        foreach ($books as $book) {
            static::assertEquals(new Author('authorOne'), $book->author());
        }
    }

    public function testReturnPaginatedBooks(): void
    {
        /** @var EventSourcedBookRepository $eventSourcedBookRepository */
        $eventSourcedBookRepository = static::getContainer()->get(EventSourcedBookRepository::class);

        /** @var QueryBusInterface $queryBus */
        $queryBus = static::getContainer()->get(QueryBusInterface::class);

        $initialBookEvents = [
            $bookId = (string) new BookId() => DummyBookFactory::createBookWasCreatedEvent(id: new BookId($bookId)),
            $bookId = (string) new BookId() => DummyBookFactory::createBookWasCreatedEvent(id: new BookId($bookId)),
            $bookId = (string) new BookId() => DummyBookFactory::createBookWasCreatedEvent(id: new BookId($bookId)),
            $bookId = (string) new BookId() => DummyBookFactory::createBookWasCreatedEvent(id: new BookId($bookId)),
            $bookId = (string) new BookId() => DummyBookFactory::createBookWasCreatedEvent(id: new BookId($bookId)),
        ];

        foreach ($initialBookEvents as $bookId => $bookEvent) {
            $eventSourcedBookRepository->save(new BookId($bookId), 0, [$bookEvent]);
        }

        /** @var Book[] $books */
        $books = iterator_to_array($queryBus->ask(new FindBooksQuery(page: 2, itemsPerPage: 2)));

        static::assertCount(2, $books);
        $i = 0;
        foreach ($books as $book) {
            static::assertEquals(array_values($initialBookEvents)[$i + 2]->id(), $book->id());
            ++$i;
        }
    }
}
