<?php

declare(strict_types=1);

namespace App\Tests\BookStore\Acceptance;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\BookStore\Domain\Event\BookWasDiscounted;
use App\BookStore\Domain\Event\BookWasUpdated;
use App\BookStore\Domain\ValueObject\BookDescription;
use App\BookStore\Domain\ValueObject\BookId;
use App\BookStore\Domain\ValueObject\Discount;
use App\BookStore\Infrastructure\Ecotone\Repository\EventSourcedBookRepository;
use App\Tests\BookStore\DummyFactory\DummyBookFactory;

final class BookEventsTest extends ApiTestCase
{
    public function testReturnBookEvents(): void
    {
        $client = static::createClient();

        /** @var EventSourcedBookRepository $eventSourcedBookRepository */
        $eventSourcedBookRepository = static::getContainer()->get(EventSourcedBookRepository::class);

        $eventSourcedBookRepository->save($bookId = new BookId(), 0, [
            DummyBookFactory::createBookWasCreatedEvent(id: $bookId),
            new BookWasUpdated(
                id: $bookId,
                description: new BookDescription('newDescription'),
            ),
            new BookWasDiscounted(
                id: $bookId,
                discount: new Discount(15),
            ),
        ]);

        $client->request('GET', sprintf('/api/books/%s/events', $bookId));

        static::assertResponseIsSuccessful();
        static::assertJsonContains([
            'hydra:member' => [
                [
                    '@type' => 'BookWasCreated',
                    'name' => ['value' => 'name'],
                    'description' => ['value' => 'description'],
                    'author' => ['value' => 'author'],
                    'content' => ['value' => 'content'],
                    'price' => ['amount' => 1000],
                ],
                [
                    '@type' => 'BookWasUpdated',
                    'description' => ['value' => 'newDescription'],
                ],
                [
                    '@type' => 'BookWasDiscounted',
                    'discount' => ['percentage' => 15],
                ],
            ],
            'hydra:totalItems' => 3,
        ]);
    }
}
