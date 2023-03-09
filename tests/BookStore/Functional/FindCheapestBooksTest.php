<?php

declare(strict_types=1);

namespace App\Tests\BookStore\Functional;

use App\BookStore\Domain\Model\Book;
use App\BookStore\Domain\Query\FindCheapestBooksQuery;
use App\BookStore\Domain\ValueObject\BookId;
use App\BookStore\Domain\ValueObject\Price;
use App\BookStore\Infrastructure\Ecotone\Repository\EventSourcedBookRepository;
use App\Shared\Application\Query\QueryBusInterface;
use App\Tests\BookStore\DummyFactory\DummyBookFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class FindCheapestBooksTest extends KernelTestCase
{
    public function testReturnOnlyTheCheapestBooks(): void
    {
        /** @var EventSourcedBookRepository $eventSourcedBookRepository */
        $eventSourcedBookRepository = static::getContainer()->get(EventSourcedBookRepository::class);

        /** @var QueryBusInterface $queryBus */
        $queryBus = static::getContainer()->get(QueryBusInterface::class);

        for ($i = 0; $i < 5; ++$i) {
            $eventSourcedBookRepository->save($bookId = new BookId(), 0, [DummyBookFactory::createBookWasCreatedEvent(id: $bookId)]);
        }

        $cheapestBooks = $queryBus->ask(new FindCheapestBooksQuery(3));

        static::assertCount(3, $cheapestBooks);
    }

    public function testReturnBooksSortedByPrice(): void
    {
        /** @var EventSourcedBookRepository $eventSourcedBookRepository */
        $eventSourcedBookRepository = static::getContainer()->get(EventSourcedBookRepository::class);

        /** @var QueryBusInterface $queryBus */
        $queryBus = static::getContainer()->get(QueryBusInterface::class);

        $prices = [2000, 1000, 3000];
        foreach ($prices as $price) {
            $eventSourcedBookRepository->save($bookId = new BookId(), 0, [DummyBookFactory::createBookWasCreatedEvent(
                id: $bookId,
                price: $price,
            )]);
        }

        /** @var Book[] $cheapestBooks */
        $cheapestBooks = $queryBus->ask(new FindCheapestBooksQuery(3));

        $sortedPrices = [1000, 2000, 3000];

        $i = 0;
        foreach ($cheapestBooks as $book) {
            static::assertEquals(new Price($sortedPrices[$i]), $book->price());
            ++$i;
        }
    }
}
