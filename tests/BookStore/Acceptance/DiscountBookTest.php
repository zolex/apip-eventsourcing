<?php

declare(strict_types=1);

namespace App\Tests\BookStore\Acceptance;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\BookStore\Domain\Repository\BookRepositoryInterface;
use App\BookStore\Domain\ValueObject\BookId;
use App\BookStore\Domain\ValueObject\Price;
use App\BookStore\Infrastructure\ApiPlatform\Resource\BookResource;
use App\BookStore\Infrastructure\Ecotone\Repository\EventSourcedBookRepository;
use App\Tests\BookStore\DummyFactory\DummyBookFactory;

final class DiscountBookTest extends ApiTestCase
{
    public function testApplyADiscountOnBook(): void
    {
        $client = static::createClient();

        /** @var EventSourcedBookRepository $eventSourcedBookRepository */
        $eventSourcedBookRepository = static::getContainer()->get(EventSourcedBookRepository::class);

        $eventSourcedBookRepository->save($bookId = new BookId(), 0, [DummyBookFactory::createBookWasCreatedEvent(id: $bookId)]);

        $client->request('POST', sprintf('/api/books/%s/discount', $bookId), [
            'json' => [
                'discountPercentage' => 20,
            ],
        ]);

        static::assertResponseIsSuccessful();
        static::assertMatchesResourceItemJsonSchema(BookResource::class);
        static::assertJsonContains(['price' => 800]);

        static::assertEquals(new Price(800), static::getContainer()->get(BookRepositoryInterface::class)->ofId($bookId)->price());
    }

    public function testValidateDiscountAmount(): void
    {
        $client = static::createClient();

        /** @var EventSourcedBookRepository $eventSourcedBookRepository */
        $eventSourcedBookRepository = static::getContainer()->get(EventSourcedBookRepository::class);

        $eventSourcedBookRepository->save($bookId = new BookId(), 0, [DummyBookFactory::createBookWasCreatedEvent(id: $bookId)]);

        $client->request('POST', sprintf('/api/books/%s/discount', $bookId), [
            'json' => [
                'discountPercentage' => 200,
            ],
        ]);

        static::assertResponseIsUnprocessable();
        static::assertJsonContains([
            'violations' => [
                ['propertyPath' => 'discountPercentage', 'message' => 'This value should be between 0 and 100.'],
            ],
        ]);

        static::assertEquals(new Price(1000), static::getContainer()->get(BookRepositoryInterface::class)->ofId($bookId)->price());
    }
}
