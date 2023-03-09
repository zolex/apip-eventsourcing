<?php

declare(strict_types=1);

namespace App\Tests\BookStore\Acceptance;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\BookStore\Domain\ValueObject\BookId;
use App\BookStore\Infrastructure\ApiPlatform\Resource\BookResource;
use App\BookStore\Infrastructure\Ecotone\Repository\EventSourcedBookRepository;
use App\Tests\BookStore\DummyFactory\DummyBookFactory;

final class CheapestBooksTest extends ApiTestCase
{
    public function testReturnOnlyTheTenCheapestBooks(): void
    {
        $client = static::createClient();

        /** @var EventSourcedBookRepository $eventSourcedBookRepository */
        $eventSourcedBookRepository = static::getContainer()->get(EventSourcedBookRepository::class);

        for ($i = 0; $i < 20; ++$i) {
            $eventSourcedBookRepository->save($bookId = new BookId(), 0, [DummyBookFactory::createBookWasCreatedEvent(
                id: $bookId,
                price: $i,
            )]);
        }

        $response = $client->request('GET', '/api/books/cheapest');

        static::assertResponseIsSuccessful();
        static::assertMatchesResourceCollectionJsonSchema(BookResource::class);

        static::assertSame(10, $response->toArray()['hydra:totalItems']);

        $prices = [];
        for ($i = 0; $i < 10; ++$i) {
            $prices[] = ['price' => $i];
        }

        static::assertJsonContains(['hydra:member' => $prices]);
    }

    public function testReturnBooksSortedByPrice(): void
    {
        $client = static::createClient();

        /** @var EventSourcedBookRepository $eventSourcedBookRepository */
        $eventSourcedBookRepository = static::getContainer()->get(EventSourcedBookRepository::class);

        $prices = [2000, 1000, 3000];
        foreach ($prices as $price) {
            $eventSourcedBookRepository->save($bookId = new BookId(), 0, [DummyBookFactory::createBookWasCreatedEvent(
                id: $bookId,
                price: $price,
            )]);
        }

        $response = $client->request('GET', '/api/books/cheapest');

        static::assertResponseIsSuccessful();
        static::assertMatchesResourceCollectionJsonSchema(BookResource::class);

        $responsePrices = array_map(fn (array $bookData): int => $bookData['price'], $response->toArray()['hydra:member']);
        static::assertSame([1000, 2000, 3000], $responsePrices);
    }
}
