<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\Ecotone\Projection;

use App\BookStore\Domain\Event\BookWasCreated;
use App\BookStore\Domain\Event\BookWasDeleted;
use App\BookStore\Domain\Event\BookWasDiscounted;
use App\BookStore\Domain\Event\BookWasUpdated;
use App\BookStore\Domain\Model\Book;
use App\BookStore\Domain\ValueObject\Price;
use Ecotone\EventSourcing\Attribute\Projection;
use Ecotone\EventSourcing\Attribute\ProjectionState;
use Ecotone\Modelling\Attribute\EventHandler;

#[Projection(self::NAME, Book::class)]
final class BookPriceProjection
{
    public const NAME = 'bookPriceList';

    /**
     * @param array<string, int> $bookPriceState
     *
     * @return array<string, int>
     */
    #[EventHandler]
    public function addBook(BookWasCreated $event, #[ProjectionState] array $bookPriceState): array
    {
        $bookPriceState[(string) $event->id()] = $event->price->amount;

        return $bookPriceState;
    }

    /**
     * @param array<string, int> $bookPriceState
     *
     * @return array<string, int>
     */
    #[EventHandler]
    public function updateBook(BookWasUpdated $event, #[ProjectionState] array $bookPriceState): array
    {
        if (!$event->price) {
            return $bookPriceState;
        }

        $bookPriceState[(string) $event->id()] = $event->price->amount;

        return $bookPriceState;
    }

    /**
     * @param array<string, int> $bookPriceState
     *
     * @return array<string, int>
     */
    #[EventHandler]
    public function discountBook(BookWasDiscounted $event, #[ProjectionState] array $bookPriceState): array
    {
        $price = new Price($bookPriceState[(string) $event->id()]);

        $bookPriceState[(string) $event->id()] = $price->applyDiscount($event->discount)->amount;

        return $bookPriceState;
    }

    /**
     * @param array<string, int> $bookPriceState
     *
     * @return array<string, int>
     */
    #[EventHandler]
    public function removeBook(BookWasDeleted $event, #[ProjectionState] array $bookPriceState): array
    {
        unset($bookPriceState[(string) $event->id()]);

        return $bookPriceState;
    }
}
